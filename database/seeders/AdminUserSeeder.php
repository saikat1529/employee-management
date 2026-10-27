<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Creates the admin only if it does not exist. An existing password is never reset.
        User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            ['name' => 'Admin', 'password' => 'password'],
        );
    }
}
