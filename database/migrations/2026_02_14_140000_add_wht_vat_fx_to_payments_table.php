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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->after('amount');
            $table->decimal('exchange_rate', 18, 6)->nullable()->default(1)->after('currency');
            $table->decimal('amount_fcy', 20, 2)->nullable()->after('exchange_rate');
            $table->decimal('amount_lcy', 20, 2)->nullable()->after('amount_fcy');
            $table->decimal('fx_rate_used', 18, 6)->nullable()->after('amount_lcy');
            $table->string('wht_treatment', 20)->nullable()->default('NONE')->after('fx_rate_used');
            $table->decimal('wht_rate', 5, 2)->default(0)->after('wht_treatment');
            $table->decimal('wht_amount', 20, 2)->default(0)->after('wht_rate');
            $table->decimal('net_payable', 20, 2)->nullable()->after('wht_amount');
            $table->decimal('total_cost', 20, 2)->nullable()->after('net_payable');
            $table->string('vat_mode', 20)->nullable()->default('NONE')->after('total_cost');
            $table->decimal('vat_amount', 20, 2)->default(0)->after('vat_mode');
            $table->decimal('base_amount', 20, 2)->nullable()->after('vat_amount');
        });

        // Add cash_deposit_id if not present (used by payment voucher)
        if (!Schema::hasColumn('payments', 'cash_deposit_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('cash_deposit_id')->nullable()->after('cheque_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'exchange_rate',
                'amount_fcy',
                'amount_lcy',
                'fx_rate_used',
                'wht_treatment',
                'wht_rate',
                'wht_amount',
                'net_payable',
                'total_cost',
                'vat_mode',
                'vat_amount',
                'base_amount',
            ]);
        });
        if (Schema::hasColumn('payments', 'cash_deposit_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('cash_deposit_id');
            });
        }
    }
};
