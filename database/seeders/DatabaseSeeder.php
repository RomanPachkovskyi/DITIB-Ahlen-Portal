<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Admin users are seeded idempotently so a fresh local DB
     * (`php artisan migrate --seed`) always has working admin logins.
     */
    public function run(): void
    {
        $admins = [
            ['email' => 'rpachkovskyi@gmail.com', 'name' => 'Roman', 'password' => 'Admin1234!'],
            ['email' => 'info@ditib-ahlen-projekte.de', 'name' => 'DITIB Ahlen', 'password' => 'AhlenDitib2026!'],
        ];

        foreach ($admins as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
