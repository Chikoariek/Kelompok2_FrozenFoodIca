<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel Detail Relasi Paket Hemat dengan Produk (Pivot / Many-to-Many)
     */
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            $table->string('bundle_id', 50);
            $table->string('product_id', 50);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // Foreign Key Constraints resmi MySQL
            $table->foreign('bundle_id')
                ->references('id')
                ->on('bundles')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};

