<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel Master Paket Promosi Hemat Toko (Bundles)
     */
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->string('id', 50)->primary(); // Kode unik: BND-01, BND-02, dll.
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('badge_left', 50)->nullable()->default('Paling Laris');
            $table->string('badge_right', 50)->nullable()->default('Hemat Promo');
            $table->json('tags')->nullable();
            $table->unsignedInteger('original_price')->default(0);
            $table->unsignedInteger('bundle_price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};

