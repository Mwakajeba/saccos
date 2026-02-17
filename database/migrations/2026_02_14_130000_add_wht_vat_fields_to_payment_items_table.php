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
        Schema::table('payment_items', function (Blueprint $table) {
            $table->decimal('base_amount', 20, 2)->nullable()->after('amount');
            $table->decimal('net_payable', 20, 2)->nullable()->after('base_amount');
            $table->decimal('total_cost', 20, 2)->nullable()->after('net_payable');
            $table->decimal('vat_amount', 20, 2)->default(0)->after('total_cost');
            $table->string('vat_mode', 20)->nullable()->default('NONE')->after('vat_amount');
            $table->decimal('wht_amount', 20, 2)->default(0)->after('vat_mode');
            $table->decimal('wht_rate', 5, 2)->default(0)->after('wht_amount');
            $table->string('wht_treatment', 20)->nullable()->default('NONE')->after('wht_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->dropColumn([
                'base_amount',
                'net_payable',
                'total_cost',
                'vat_amount',
                'vat_mode',
                'wht_amount',
                'wht_rate',
                'wht_treatment',
            ]);
        });
    }
};
