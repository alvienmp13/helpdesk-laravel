<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['hardware', 'Hardware', 'laptop', 'Laptop, PC...'],
            ['software', 'Software', 'apps', 'Office, Figma...'],
            ['network', 'Jaringan', 'network', 'Wi-Fi, VPN...'],
            ['access', 'Akses & Akun', 'user-key', 'Password, Role...'],
            ['email', 'Email & SSO', 'mail-check', 'Google, Outlook...'],
        ] as [$slug, $name, $icon, $description]) {
            Category::firstOrCreate(compact('slug'), compact('name', 'icon', 'description'));
        }
    }
}
