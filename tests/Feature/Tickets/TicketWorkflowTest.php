<?php

namespace Tests\Feature\Tickets;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\CategorySeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\AuthTestCase;

class TicketWorkflowTest extends AuthTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        Storage::fake('ticket_private');
    }

    private function png(string $name): \Illuminate\Http\Testing\File
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aZ1cAAAAASUVORK5CYII='));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'intent' => 'submit', 'title' => 'VPN kantor tidak terhubung',
            'description' => 'Koneksi VPN gagal sejak pagi, sudah mencoba memulai ulang laptop.',
            'category_id' => Category::first()->id, 'priority' => 'medium',
            'department' => 'Engineering', 'location' => 'Remote / Lokasi Lain',
        ], $overrides);
    }

    private function ticket(User $user, array $overrides = []): Ticket
    {
        return app(TicketService::class)->save($user, $this->payload($overrides), []);
    }

    public function test_user_creates_ticket_with_unique_number_and_initial_history(): void
    {
        $user = $this->account();
        $this->actingAs($user)->post(route('user.tickets.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $ticket = Ticket::sole();
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame(TicketStatus::Open, $ticket->status);
        $this->assertNull($ticket->assigned_to);
        $this->assertStringStartsWith('TK-', $ticket->ticket_number);
        $this->assertDatabaseHas('ticket_status_histories', ['ticket_id' => $ticket->id, 'user_id' => $user->id, 'from_status' => null, 'to_status' => 'open']);
        $second = $this->ticket($user);
        $this->assertNotSame($ticket->ticket_number, $second->ticket_number);
        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee($ticket->title)->assertSee('name="_token"', false);
    }

    public function test_incomplete_draft_can_be_saved_edited_then_submitted(): void
    {
        $user = $this->account();
        $this->actingAs($user)->post(route('user.tickets.store'), ['intent' => 'draft', 'priority' => 'low'])->assertSessionHasNoErrors()->assertRedirect();
        $ticket = Ticket::sole();
        $this->assertSame(TicketStatus::Draft, $ticket->status);
        $this->post(route('user.tickets.submit', $ticket))->assertSessionHasErrors(['category_id', 'description']);
        $this->get(route('user.tickets.edit', $ticket))->assertOk()->assertSee('Edit Draft');
        $this->patch(route('user.tickets.update', $ticket), $this->payload(['intent' => 'draft']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('ticket_status_histories', 1);
        $this->post(route('user.tickets.submit', $ticket))->assertSessionHasNoErrors()->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
        $this->assertDatabaseHas('ticket_status_histories', ['ticket_id' => $ticket->id, 'from_status' => 'draft', 'to_status' => 'open']);
        $this->post(route('user.tickets.submit', $ticket))->assertForbidden();
        $this->assertDatabaseCount('ticket_status_histories', 2);
    }

    public function test_edit_form_can_submit_a_draft_directly(): void
    {
        $user = $this->account();
        $ticket = $this->ticket($user, ['intent' => 'draft']);
        $this->actingAs($user)->patch(route('user.tickets.update', $ticket), $this->payload(['title' => 'Judul final laporan']))->assertSessionHasNoErrors();
        $this->assertSame('Judul final laporan', $ticket->fresh()->title);
        $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
        $this->assertDatabaseCount('ticket_status_histories', 2);
    }

    public function test_owner_can_delete_only_drafts_and_private_files_are_removed(): void
    {
        $user = $this->account();
        $this->actingAs($user)->post(route('user.tickets.store'), $this->payload(['intent' => 'draft', 'attachments' => [$this->png('bukti.png')]]))->assertSessionHasNoErrors();
        $ticket = Ticket::sole();
        $path = $ticket->attachments()->sole()->path;
        Storage::disk('ticket_private')->assertExists($path);
        $this->delete(route('user.tickets.destroy', $ticket))->assertRedirect(route('user.tickets'));
        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('ticket_attachments', 0);
        $this->assertDatabaseCount('ticket_status_histories', 0);
        Storage::disk('ticket_private')->assertMissing($path);
        $open = $this->ticket($user);
        $this->delete(route('user.tickets.destroy', $open))->assertForbidden();
        $this->get(route('user.tickets.edit', $open))->assertForbidden();
        $this->patch(route('user.tickets.update', $open), $this->payload())->assertForbidden();
    }

    public function test_lists_search_filters_pagination_and_statistics_are_scoped_to_owner(): void
    {
        $owner = $this->account();
        $own = $this->ticket($owner, ['title' => 'Printer milik saya']);
        $this->ticket($owner, ['intent' => 'draft', 'title' => 'Draft saya']);
        $secret = $this->ticket($this->account(), ['title' => 'Rahasia milik orang lain']);
        $this->actingAs($owner)->get(route('user.tickets'))->assertOk()->assertSee('Printer milik saya')->assertDontSee($secret->title)
            ->assertViewHas('counts', fn ($counts) => $counts['all'] === 2 && $counts['active'] === 1 && $counts['draft'] === 1);
        $this->get(route('user.tickets', ['q' => $own->ticket_number]))->assertViewHas('tickets', fn ($tickets) => count($tickets) === 1 && $tickets[0]['key'] === $own->id);
        $this->get(route('user.tickets', ['q' => 'Printer', 'status' => 'open', 'priority' => 'medium']))->assertViewHas('pagination', fn ($page) => $page->total() === 1);
        $this->get(route('user.tickets', ['status' => 'draft']))->assertViewHas('tickets', fn ($tickets) => count($tickets) === 1 && $tickets[0]['status_value'] === 'draft');
        $this->get(route('user.tickets', ['q' => '%']))->assertViewHas('pagination', fn ($page) => $page->total() === 0);
        $this->get(route('user.dashboard'))->assertOk()->assertDontSee($secret->title)->assertViewHas('statistics', fn ($stats) => $stats[0]['value'] === 2)->assertViewHas('activeCount', 1);
        for ($i = 0; $i < 10; $i++) $this->ticket($owner, ['title' => 'Tambahan tiket '.$i]);
        $this->get(route('user.tickets', ['page' => 2]))->assertViewHas('pagination', fn ($page) => $page->total() === 12 && $page->count() === 2);
    }

    public function test_user_cannot_read_edit_delete_submit_message_or_manage_another_users_ticket(): void
    {
        $other = $this->ticket($this->account(), ['intent' => 'draft']);
        $this->actingAs($this->account());
        $this->get(route('tickets.show', $other))->assertForbidden();
        $this->get(route('user.tickets.edit', $other))->assertForbidden();
        $this->patch(route('user.tickets.update', $other), $this->payload())->assertForbidden();
        $this->post(route('user.tickets.submit', $other))->assertForbidden();
        $this->delete(route('user.tickets.destroy', $other))->assertForbidden();
        $this->post(route('tickets.messages.store', $other), ['body' => 'Intrusi'])->assertForbidden();
        $this->patch(route('tickets.status', $other), ['status' => 'in_progress'])->assertForbidden();
        $this->patch(route('tickets.assign', $other), ['assigned_to' => null])->assertForbidden();
        $this->assertSame(TicketStatus::Draft, $other->fresh()->status);
    }

    public function test_agent_access_is_limited_to_assigned_tickets(): void
    {
        $user = $this->account();
        $assigned = $this->ticket($user, ['title' => 'Ditugaskan kepada agent']);
        $other = $this->ticket($user, ['title' => 'Tidak ditugaskan kepada agent']);
        $agent = $this->account(Role::Agent);
        app(TicketService::class)->assign($this->account(Role::Admin), $assigned, $agent->id);
        $this->actingAs($agent)->get(route('support.tickets'))->assertOk()->assertSee($assigned->title)->assertDontSee($other->title);
        $this->get(route('tickets.show', $assigned))->assertOk();
        $this->get(route('tickets.show', $other))->assertForbidden();
        $this->patch(route('tickets.status', $other), ['status' => 'in_progress'])->assertForbidden();
        $this->post(route('tickets.messages.store', $other), ['body' => 'Tidak boleh'])->assertForbidden();
        $this->patch(route('tickets.assign', $assigned), ['assigned_to' => $agent->id])->assertForbidden();
        $this->patch(route('tickets.status', $assigned), ['status' => 'in_progress'])->assertSessionHasNoErrors();
        $this->post(route('tickets.messages.store', $assigned), ['body' => 'Sedang diperiksa'])->assertSessionHasNoErrors();
        $this->assertSame(TicketStatus::InProgress, $assigned->fresh()->status);
    }

    public function test_admin_can_view_every_ticket_assign_agents_and_manage_status(): void
    {
        $one = $this->ticket($this->account(), ['title' => 'Laporan pertama']);
        $two = $this->ticket($this->account(), ['title' => 'Laporan kedua']);
        $draft = $this->ticket($this->account(), ['intent' => 'draft']);
        $admin = $this->account(Role::Admin);
        $agent = $this->account(Role::Agent);
        $this->actingAs($admin)->get(route('support.tickets'))->assertOk()->assertSee($one->title)->assertSee($two->title);
        $this->get(route('tickets.show', $draft))->assertOk();
        $this->patch(route('tickets.assign', $one), ['assigned_to' => $agent->id])->assertSessionHasNoErrors();
        $this->assertSame($agent->id, $one->fresh()->assigned_to);
        $this->patch(route('tickets.status', $one), ['status' => 'in_progress', 'note' => 'Mulai investigasi'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ticket_status_histories', ['ticket_id' => $one->id, 'user_id' => $admin->id, 'from_status' => 'open', 'to_status' => 'in_progress', 'note' => 'Mulai investigasi']);
        $this->patch(route('tickets.assign', $two), ['assigned_to' => $admin->id])->assertSessionHasErrors('assigned_to');
        $this->patch(route('tickets.assign', $draft), ['assigned_to' => $agent->id])->assertForbidden();
        $this->patch(route('tickets.assign', $one), ['assigned_to' => null])->assertSessionHasNoErrors();
        $this->assertNull($one->fresh()->assigned_to);
    }

    public function test_status_transitions_record_history_and_resolution_timestamps(): void
    {
        $user = $this->account();
        $ticket = $this->ticket($user);
        $admin = $this->account(Role::Admin);
        $this->actingAs($admin);
        $this->patch(route('tickets.status', $ticket), ['status' => 'closed'])->assertSessionHasErrors('status');
        foreach (['in_progress', 'waiting_user', 'resolved', 'in_progress', 'resolved', 'closed'] as $status) {
            $this->patch(route('tickets.status', $ticket), ['status' => $status])->assertSessionHasNoErrors();
            $ticket->refresh();
            $this->assertSame($status, $ticket->status->value);
            if (in_array($status, ['resolved', 'closed'], true)) $this->assertNotNull($ticket->resolved_at);
            else $this->assertNull($ticket->resolved_at);
        }
        $this->assertSame(['open', 'in_progress', 'waiting_user', 'resolved', 'in_progress', 'resolved', 'closed'], $ticket->statusHistories()->pluck('to_status')->all());
        $this->patch(route('tickets.status', $ticket), ['status' => 'in_progress'])->assertForbidden();
        $this->actingAs($user)->post(route('tickets.messages.store', $ticket), ['body' => 'Pesan pada arsip'])->assertForbidden();
    }

    public function test_user_cannot_change_status_or_assignment_even_for_owned_ticket(): void
    {
        $user = $this->account();
        $ticket = $this->ticket($user);
        $this->actingAs($user)->patch(route('tickets.status', $ticket), ['status' => 'resolved'])->assertForbidden();
        $this->patch(route('tickets.assign', $ticket), ['assigned_to' => null])->assertForbidden();
        $this->assertDatabaseCount('ticket_status_histories', 1);
    }

    public function test_messages_and_uploads_persist_and_downloads_are_authorized(): void
    {
        $user = $this->account();
        $ticket = $this->ticket($user);
        $agent = $this->account(Role::Agent);
        $admin = $this->account(Role::Admin);
        app(TicketService::class)->assign($admin, $ticket, $agent->id);
        $this->actingAs($user)->post(route('tickets.messages.store', $ticket), [
            'body' => '<script>alert(1)</script>', 'attachments' => [$this->png('bukti.png')],
        ])->assertSessionHasNoErrors();
        $attachment = TicketAttachment::sole();
        $this->assertNotNull($attachment->ticket_message_id);
        Storage::disk('ticket_private')->assertExists($attachment->path);
        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        foreach ([$user, $agent, $admin] as $actor) {
            $this->actingAs($actor)->get(route('tickets.attachments.download', $attachment))->assertOk()->assertDownload('bukti.png')->assertHeader('X-Content-Type-Options', 'nosniff');
        }
        foreach ([$this->account(), $this->account(Role::Agent)] as $outsider) {
            $this->actingAs($outsider)->get(route('tickets.attachments.download', $attachment))->assertForbidden();
        }
        $this->get('/storage/'.$attachment->path)->assertForbidden();
        $this->assertFalse(Storage::disk('public')->exists($attachment->path));
        $this->actingAs($agent)->post(route('tickets.messages.store', $ticket), ['body' => 'Sudah diperiksa'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('ticket_messages', 2);
    }

    public function test_validation_rejects_invalid_fields_and_sensitive_mass_assignment(): void
    {
        $this->actingAs($this->account());
        $this->post(route('user.tickets.store'), $this->payload(['title' => 'x', 'category_id' => 99999, 'priority' => 'critical', 'description' => 'x']))
            ->assertSessionHasErrors(['title', 'category_id', 'priority', 'description']);
        foreach (['user_id' => 99, 'assigned_to' => 99, 'status' => 'closed', 'resolved_at' => '2026-01-01', 'ticket_number' => 'TK-forged'] as $key => $value) {
            $this->post(route('user.tickets.store'), $this->payload([$key => $value]))->assertSessionHasErrors($key);
        }
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_upload_validation_checks_size_mime_extension_count_and_message_content(): void
    {
        $user = $this->account();
        $ticket = $this->ticket($user);
        $this->actingAs($user);
        $invalid = [
            $this->png('large.png')->size(5121),
            UploadedFile::fake()->createWithContent('fake.png', '<?php echo "not an image";'),
            UploadedFile::fake()->createWithContent('text.png', 'This is plain text, not an image.'),
            UploadedFile::fake()->createWithContent('script.php', 'plain text file'),
            UploadedFile::fake()->createWithContent('empty.log', ''),
        ];
        foreach ($invalid as $file) {
            // The framework fake guesses MIME from the name; use actual bytes for these checks.
            $file->mimeType((new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname()));
            $this->post(route('user.tickets.store'), $this->payload(['attachments' => [$file]]))->assertSessionHasErrors('attachments.0');
        }
        $files = array_map(fn ($i) => $this->png("file-$i.png"), range(1, 6));
        $this->post(route('tickets.messages.store', $ticket), ['attachments' => $files])->assertSessionHasErrors('attachments');
        $this->post(route('tickets.messages.store', $ticket), ['body' => '   '])->assertSessionHasErrors('body');
        $this->post(route('tickets.messages.store', $ticket), ['body' => str_repeat('a', 2001)])->assertSessionHasErrors('body');
        $this->assertDatabaseCount('ticket_messages', 0);
        $this->assertDatabaseCount('ticket_attachments', 0);
        $this->post(route('tickets.messages.store', $ticket), ['attachments' => [UploadedFile::fake()->createWithContent('error.log', 'VPN connection timeout')]])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('ticket_messages', 1);
        $this->assertDatabaseCount('ticket_attachments', 1);
    }

    public function test_file_failure_rolls_back_ticket_history_and_files(): void
    {
        $user = $this->account();
        $badFile = \Mockery::mock(UploadedFile::class);
        $badFile->shouldReceive('store')->once()->andThrow(new \RuntimeException('Simulated disk failure'));
        try {
            app(TicketService::class)->save($user, $this->payload(), [$this->png('first.png'), $badFile]);
            $this->fail('Expected disk failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated disk failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('ticket_status_histories', 0);
        $this->assertDatabaseCount('ticket_attachments', 0);
        $this->assertSame([], Storage::disk('ticket_private')->allFiles());
    }

    public function test_stale_agent_authorization_is_rechecked_after_reassignment(): void
    {
        $ticket = $this->ticket($this->account());
        $agent = $this->account(Role::Agent);
        $admin = $this->account(Role::Admin);
        $service = app(TicketService::class);
        $service->assign($admin, $ticket, $agent->id);
        $stale = $ticket->fresh();
        $service->assign($admin, $ticket, $this->account(Role::Agent)->id);
        try {
            $service->changeStatus($agent, $stale, TicketStatus::InProgress, null);
            $this->fail('Stale assignee must be denied');
        } catch (AuthorizationException) {
            $this->assertSame(TicketStatus::Open, $ticket->fresh()->status);
            $this->assertDatabaseCount('ticket_status_histories', 1);
        }
    }

    public function test_ticket_mutations_require_csrf_tokens(): void
    {
        $user = $this->account();
        $ticket = $this->ticket($user, ['intent' => 'draft']);
        $this->actingAs($user)->withSession(['_token' => 'valid-session-token']);
        $this->app->instance('env', 'local');
        foreach ([
            ['POST', route('user.tickets.store')], ['PATCH', route('user.tickets.update', $ticket)],
            ['DELETE', route('user.tickets.destroy', $ticket)], ['POST', route('user.tickets.submit', $ticket)],
            ['POST', route('tickets.messages.store', $ticket)], ['PATCH', route('tickets.status', $ticket)],
            ['PATCH', route('tickets.assign', $ticket)],
        ] as [$method, $url]) $this->call($method, $url)->assertStatus(419);
    }

    public function test_category_seeding_is_idempotent(): void
    {
        $this->seed(CategorySeeder::class);
        $this->assertDatabaseCount('categories', 5);
    }

    public function test_guests_cannot_read_or_mutate_tickets(): void
    {
        $ticket = $this->ticket($this->account());
        $this->get(route('tickets.show', $ticket))->assertRedirect(route('login'));
        $this->get(route('support.tickets'))->assertRedirect(route('login'));
        $this->post(route('user.tickets.store'), $this->payload())->assertRedirect(route('login'));
        $this->postJson(route('tickets.messages.store', $ticket), ['body' => 'Guest'])->assertUnauthorized();
        $this->patchJson(route('tickets.status', $ticket), ['status' => 'in_progress'])->assertUnauthorized();
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('ticket_messages', 0);
    }

    public function test_draft_update_rejects_protected_fields_and_preserves_existing_attachment_limit(): void
    {
        $user = $this->account();
        $files = array_map(fn ($i) => $this->png("proof-$i.png"), range(1, 5));
        $this->actingAs($user)->post(route('user.tickets.store'), $this->payload(['intent' => 'draft', 'attachments' => $files]))->assertSessionHasNoErrors();
        $ticket = Ticket::sole();
        $this->patch(route('user.tickets.update', $ticket), $this->payload(['intent' => 'draft', 'user_id' => 999]))->assertSessionHasErrors('user_id');
        $this->patch(route('user.tickets.update', $ticket), $this->payload(['intent' => 'draft', 'title' => 'Judul tidak boleh tersimpan', 'attachments' => [$this->png('extra.png')]]))->assertSessionHasErrors('attachments');
        $this->assertSame($user->id, $ticket->fresh()->user_id);
        $this->assertSame($this->payload()['title'], $ticket->fresh()->title);
        $this->assertDatabaseCount('ticket_attachments', 5);
        $this->assertCount(5, Storage::disk('ticket_private')->allFiles());
    }

    public function test_reassignment_removes_previous_agents_download_permission(): void
    {
        $user = $this->account();
        $agent = $this->account(Role::Agent);
        $admin = $this->account(Role::Admin);
        $this->actingAs($user)->post(route('user.tickets.store'), $this->payload(['attachments' => [$this->png('private.png')]]))->assertSessionHasNoErrors();
        $ticket = Ticket::sole();
        $attachment = TicketAttachment::sole();
        $service = app(TicketService::class);
        $service->assign($admin, $ticket, $agent->id);
        $this->actingAs($agent)->get(route('tickets.attachments.download', $attachment))->assertOk();
        $service->assign($admin, $ticket, null);
        $this->get(route('tickets.attachments.download', $attachment))->assertForbidden();
        $this->post(route('tickets.messages.store', $ticket), ['body' => 'Akses lama'])->assertForbidden();
    }
}
