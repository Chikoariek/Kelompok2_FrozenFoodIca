<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengoptimalkan panjang karakter (char length) kolom agar logis, efisien,
     * dan selaras dengan rancangan basis data.
     */
    public function up(): void
    {
        // 1. Table users (Pengguna & Pelanggan)
        DB::statement("ALTER TABLE users MODIFY phone VARCHAR(20) NULL");
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'user'");

        // 2. Table categories (Kategori Produk)
        DB::statement("ALTER TABLE categories MODIFY name VARCHAR(50) NOT NULL");

        // 3. Table products (Data Produk Makanan Beku)
        DB::statement("ALTER TABLE products MODIFY id VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE products MODIFY name VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE products MODIFY category VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE products MODIFY weight VARCHAR(30) NULL DEFAULT '500g'");

        // 4. Table orders (Transaksi Pesanan)
        DB::statement("ALTER TABLE orders MODIFY id VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY customer_name VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY customer_phone VARCHAR(20) NULL");
        DB::statement("ALTER TABLE orders MODIFY order_date VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY method VARCHAR(50) NOT NULL DEFAULT 'Ambil di Toko / Self Pick-up'");
        DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(50) NOT NULL DEFAULT 'Tunai (Cash)'");
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Diproses'");
        DB::statement("ALTER TABLE orders MODIFY channel VARCHAR(30) NOT NULL DEFAULT 'Online'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY phone VARCHAR(255) NULL");
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NOT NULL DEFAULT 'user'");

        DB::statement("ALTER TABLE categories MODIFY name VARCHAR(255) NOT NULL");

        DB::statement("ALTER TABLE products MODIFY id VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE products MODIFY name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE products MODIFY category VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE products MODIFY weight VARCHAR(255) NULL DEFAULT '500g'");

        DB::statement("ALTER TABLE orders MODIFY id VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY customer_name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY customer_phone VARCHAR(255) NULL");
        DB::statement("ALTER TABLE orders MODIFY order_date VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY method VARCHAR(255) NOT NULL DEFAULT 'Ambil di Toko / Self Pick-up'");
        DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(255) NOT NULL DEFAULT 'Tunai (Cash)'");
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(255) NOT NULL DEFAULT 'Diproses'");
        DB::statement("ALTER TABLE orders MODIFY channel VARCHAR(255) NOT NULL DEFAULT 'Online'");
    }
};
