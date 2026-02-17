<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add WHT/VAT columns to receipt_items (align with ReceiptItem model).
     */
    public function up(): void
    {
        Schema::table('receipt_items', function (Blueprint $table) {
            if (!Schema::hasColumn('receipt_items', 'wht_treatment')) {
                $table->string('wht_treatment', 20)->nullable()->default('NONE')->after('amount');
            }
            if (!Schema::hasColumn('receipt_items', 'wht_rate')) {
                $table->decimal('wht_rate', 5, 2)->default(0)->after('wht_treatment');
            }
            if (!Schema::hasColumn('receipt_items', 'wht_amount')) {
                $table->decimal('wht_amount', 20, 2)->default(0)->after('wht_rate');
            }
            if (!Schema::hasColumn('receipt_items', 'base_amount')) {
                $table->decimal('base_amount', 20, 2)->nullable()->after('wht_amount');
            }
            if (!Schema::hasColumn('receipt_items', 'net_receivable')) {
                $table->decimal('net_receivable', 20, 2)->nullable()->after('base_amount');
            }
            if (!Schema::hasColumn('receipt_items', 'vat_mode')) {
                $table->string('vat_mode', 20)->nullable()->default('NONE')->after('net_receivable');
            }
            if (!Schema::hasColumn('receipt_items', 'vat_amount')) {
                $table->decimal('vat_amount', 20, 2)->default(0)->after('vat_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'wht_treatment', 'wht_rate', 'wht_amount', 'base_amount',
            'net_receivable', 'vat_mode', 'vat_amount',
        ];
        $existing = array_filter($columns, fn($col) => Schema::hasColumn('receipt_items', $col));
        if (!empty($existing)) {
            Schema::table('receipt_items', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
