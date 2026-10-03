<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary(); // Support SKU or PRD-xxx (e.g. 'P01', 'PRD-123')
            $table->string('name');
            $table->string('category');
            $table->unsignedInteger('price')->default(0);
            $table->integer('stock')->default(0);
            $table->string('weight')->nullable()->default('500g');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->json('tags')->nullable();
            $table->string('temperature')->nullable()->default('-18°C');
            $table->string('shelfLife')->nullable()->default('6 Bulan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
