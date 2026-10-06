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
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. ORD-ICA-1024 or POS-ICA-9120
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('order_date');
            $table->string('method')->default('Ambil di Toko / Self Pick-up');
            $table->string('payment_method')->default('Tunai (Cash)');
            $table->string('status')->default('Diproses');
            $table->json('items');
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('ice_fee')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->boolean('is_paid')->default(false);
            $table->string('channel')->default('Online');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
