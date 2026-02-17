<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add currency, FX and WHT/VAT columns to receipts table (align with payments and Receipt model).
     */
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'currency')) {
                $table->string('currency', 10)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('receipts', 'exchange_rate')) {
                $table->decimal('exchange_rate', 18, 6)->nullable()->default(1)->after('currency');
            }
            if (!Schema::hasColumn('receipts', 'amount_fcy')) {
                $table->decimal('amount_fcy', 20, 2)->nullable()->after('exchange_rate');
            }
            if (!Schema::hasColumn('receipts', 'amount_lcy')) {
                $table->decimal('amount_lcy', 20, 2)->nullable()->after('amount_fcy');
            }
            if (!Schema::hasColumn('receipts', 'fx_rate_used')) {
                $table->decimal('fx_rate_used', 18, 6)->nullable()->after('amount_lcy');
            }
            if (!Schema::hasColumn('receipts', 'wht_treatment')) {
                $table->string('wht_treatment', 20)->nullable()->default('NONE')->after('fx_rate_used');
            }
            if (!Schema::hasColumn('receipts', 'wht_rate')) {
                $table->decimal('wht_rate', 5, 2)->default(0)->after('wht_treatment');
            }
            if (!Schema::hasColumn('receipts', 'wht_amount')) {
                $table->decimal('wht_amount', 20, 2)->default(0)->after('wht_rate');
            }
            if (!Schema::hasColumn('receipts', 'net_receivable')) {
                $table->decimal('net_receivable', 20, 2)->nullable()->after('wht_amount');
            }
            if (!Schema::hasColumn('receipts', 'vat_mode')) {
                $table->string('vat_mode', 20)->nullable()->default('NONE')->after('net_receivable');
            }
            if (!Schema::hasColumn('receipts', 'vat_amount')) {
                $table->decimal('vat_amount', 20, 2)->default(0)->after('vat_mode');
            }
            if (!Schema::hasColumn('receipts', 'base_amount')) {
                $table->decimal('base_amount', 20, 2)->nullable()->after('vat_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'currency', 'exchange_rate', 'amount_fcy', 'amount_lcy', 'fx_rate_used',
            'wht_treatment', 'wht_rate', 'wht_amount', 'net_receivable',
            'vat_mode', 'vat_amount', 'base_amount',
        ];
        $existing = array_filter($columns, fn($col) => Schema::hasColumn('receipts', $col));
        if (!empty($existing)) {
            Schema::table('receipts', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
