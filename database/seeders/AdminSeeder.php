<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Buat akun admin default untuk Ica Frozen Food.
     */
    public function run(): void
    {
        // 1. Super Admin Utama
        User::updateOrCreate(
            ['email' => 'admin@icafrozenfood.com'],
            [
                'name'     => 'Admin Ica',
                'password' => Hash::make('admin123'),
                'phone'    => '0878-5451-3770',
                'address'  => 'Loktabat Utara, Kec. Banjarbaru Utara, Kota Banjar Baru, Kalimantan Selatan 70714',
                'is_admin' => true,
                'role'     => 'admin',
            ]
        );

        // 2. Kasir Toko (Admin POS)
        User::updateOrCreate(
            ['email' => 'kasir@icafrozenfood.com'],
            [
                'name'     => 'Kasir Ica',
                'password' => Hash::make('kasir123'),
                'phone'    => '0878-5451-3770',
                'address'  => 'Area Kasir Toko Ica Frozen Food',
                'is_admin' => true,
                'role'     => 'admin',
            ]
        );

        // 3. Pelanggan 1 (Customer Walk-In / Online)
        User::updateOrCreate(
            ['email' => 'budi@example.com'],
            [
                'name'     => 'Budi Doremi',
                'phone'    => '0812-3456-7890',
                'address'  => 'Jl. Flamboyan No. 12, RT 02/RW 05, Banjarbaru',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'role'     => 'user',
            ]
        );

        // 4. Pelanggan 2 (Customer Rumah)
        User::updateOrCreate(
            ['email' => 'siti@example.com'],
            [
                'name'     => 'Siti Rahma',
                'phone'    => '0878-1122-3344',
                'address'  => 'Komp. Permata Hijau Blok C No. 8, Loktabat Utara',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'role'     => 'user',
            ]
        );

        $this->command->info('✅ Akun dummy siap digunakan!');
    }
}
