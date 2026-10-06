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
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'temperature')) {
                $table->dropColumn('temperature');
            }
            if (Schema::hasColumn('products', 'shelfLife')) {
                $table->dropColumn('shelfLife');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('temperature')->nullable()->default('-18°C');
            $table->string('shelfLife')->nullable()->default('6 Bulan');
        });
    }
};
