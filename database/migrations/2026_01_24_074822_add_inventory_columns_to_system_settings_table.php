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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('inventory_cost_method', 50)->nullable()->after('id');
            $table->boolean('enable_negative_stock')->default(false)->after('inventory_cost_method');
            $table->boolean('auto_generate_item_codes')->default(false)->after('enable_negative_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['inventory_cost_method', 'enable_negative_stock', 'auto_generate_item_codes']);
        });
    }
};
