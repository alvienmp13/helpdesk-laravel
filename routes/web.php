<?php

use App\Http\Controllers\User\ChatController;
use App\Http\Controllers\User\CreateTicketController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\TicketController;
use App\Http\Controllers\TicketActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (Request $request) => redirect()->route($request->user()?->dashboardRoute() ?? 'login'))->name('home');
Route::get('/dashboard', fn (Request $request) => redirect()->route($request->user()->dashboardRoute()))->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tickets', TicketController::class)->name('tickets');
    Route::get('/tickets/create', CreateTicketController::class)->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}/edit', [CreateTicketController::class, 'edit'])->name('tickets.edit');
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/submit', [TicketController::class, 'submit'])->name('tickets.submit');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::get('/chat', ChatController::class)->name('chat');
});

Route::get('/support/tickets', TicketController::class)->middleware(['auth', 'role:agent,admin'])->name('support.tickets');
Route::middleware(['auth', 'role:user,agent,admin'])->group(function () {
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/messages', [TicketActivityController::class, 'message'])->name('tickets.messages.store');
    Route::patch('/tickets/{ticket}/status', [TicketActivityController::class, 'status'])->name('tickets.status');
    Route::patch('/tickets/{ticket}/assignment', [TicketActivityController::class, 'assign'])->name('tickets.assign');
    Route::get('/ticket-attachments/{attachment}/download', [TicketActivityController::class, 'download'])->name('tickets.attachments.download');
});

Route::view('/agent/dashboard', 'staff.dashboard', ['role' => 'agent'])->middleware(['auth', 'role:agent'])->name('agent.dashboard');
Route::view('/admin/dashboard', 'staff.dashboard', ['role' => 'admin'])->middleware(['auth', 'role:admin'])->name('admin.dashboard');

require __DIR__.'/auth.php';
