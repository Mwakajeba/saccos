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
        Schema::table('bank_reconciliation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_reconciliation_items', 'item_type')) {
                $table->string('item_type', 20)->nullable()->after('transaction_type')
                    ->comment('DNC, UPC, BANK_ERROR');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'origin_date')) {
                $table->date('origin_date')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'origin_month')) {
                $table->date('origin_month')->nullable()->after('origin_date');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'origin_reconciliation_id')) {
                $table->foreignId('origin_reconciliation_id')->nullable()
                    ->after('origin_month')
                    ->constrained('bank_reconciliations')->onDelete('set null');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'age_days')) {
                $table->unsignedInteger('age_days')->nullable()->after('origin_reconciliation_id');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'age_months')) {
                $table->decimal('age_months', 10, 2)->nullable()->after('age_days');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'uncleared_status')) {
                $table->string('uncleared_status', 20)->nullable()->after('age_months')
                    ->comment('UNCLEARED, CLEARED, MANUALLY_CLOSED');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'clearing_date')) {
                $table->date('clearing_date')->nullable()->after('uncleared_status');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'clearing_month')) {
                $table->date('clearing_month')->nullable()->after('clearing_date');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'cleared_by')) {
                $table->foreignId('cleared_by')->nullable()
                    ->after('clearing_month')
                    ->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'clearing_reference')) {
                $table->string('clearing_reference')->nullable()->after('cleared_by');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'manual_close_reason')) {
                $table->text('manual_close_reason')->nullable()->after('clearing_reference');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'manual_closed_by')) {
                $table->foreignId('manual_closed_by')->nullable()
                    ->after('manual_close_reason')
                    ->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'manual_closed_at')) {
                $table->timestamp('manual_closed_at')->nullable()->after('manual_closed_by');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'is_brought_forward')) {
                $table->boolean('is_brought_forward')->default(false)->after('manual_closed_at');
            }
            if (!Schema::hasColumn('bank_reconciliation_items', 'brought_forward_from_item_id')) {
                $table->foreignId('brought_forward_from_item_id')->nullable()
                    ->after('is_brought_forward')
                    ->constrained('bank_reconciliation_items')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_reconciliation_items', function (Blueprint $table) {
            $columns = [
                'item_type', 'origin_date', 'origin_month', 'origin_reconciliation_id',
                'age_days', 'age_months', 'uncleared_status', 'clearing_date', 'clearing_month',
                'cleared_by', 'clearing_reference', 'manual_close_reason', 'manual_closed_by',
                'manual_closed_at', 'is_brought_forward', 'brought_forward_from_item_id',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('bank_reconciliation_items', $col)) {
                    if (in_array($col, ['origin_reconciliation_id', 'cleared_by', 'manual_closed_by', 'brought_forward_from_item_id'])) {
                        $table->dropForeign([$col]);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
