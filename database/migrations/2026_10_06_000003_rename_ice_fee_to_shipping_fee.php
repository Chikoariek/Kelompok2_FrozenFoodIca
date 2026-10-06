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
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'ice_fee') && !Schema::hasColumn('orders', 'shipping_fee')) {
                $table->renameColumn('ice_fee', 'shipping_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_fee') && !Schema::hasColumn('orders', 'ice_fee')) {
                $table->renameColumn('shipping_fee', 'ice_fee');
            }
        });
    }
};
