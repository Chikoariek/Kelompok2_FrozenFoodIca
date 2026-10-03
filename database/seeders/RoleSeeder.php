<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan akun lama agar hanya tersisa akun admin dan user baru
        User::whereNotIn('email', ['admin@example.com', 'user@example.com'])->delete();

        // Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'is_admin' => true,
            ]
        );

        // Akun User Biasa
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'     => 'Regular User',
                'password' => Hash::make('password123'),
                'role'     => 'user',
                'is_admin' => false,
            ]
        );
    }
}
