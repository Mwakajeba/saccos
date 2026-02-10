<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: SQLite uses string for enum-like columns, so 'pending_receipt' is automatically allowed.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('petty_cash_transactions', 'receipt_status')) {
            Schema::table('petty_cash_transactions', function (Blueprint $table) {
                $table->string('receipt_status')->nullable();
            });
        }
        if (!Schema::hasColumn('petty_cash_transactions', 'receipt_verified_by')) {
            Schema::table('petty_cash_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('receipt_verified_by')->nullable();
                $table->foreign('receipt_verified_by')->references('id')->on('users')->onDelete('set null');
            });
        }
        if (!Schema::hasColumn('petty_cash_transactions', 'receipt_verified_at')) {
            Schema::table('petty_cash_transactions', function (Blueprint $table) {
                $table->timestamp('receipt_verified_at')->nullable();
            });
        }
        if (!Schema::hasColumn('petty_cash_transactions', 'receipt_verification_notes')) {
            Schema::table('petty_cash_transactions', function (Blueprint $table) {
                $table->text('receipt_verification_notes')->nullable();
            });
        }
        if (!Schema::hasColumn('petty_cash_transactions', 'disbursed_by')) {
            Schema::table('petty_cash_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('disbursed_by')->nullable();
                $table->foreign('disbursed_by')->references('id')->on('users')->onDelete('set null');
            });
        }
        if (!Schema::hasColumn('petty_cash_transactions', 'disbursed_at')) {
            Schema::table('petty_cash_transactions', function (Blueprint $table) {
                $table->timestamp('disbursed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('petty_cash_transactions', 'receipt_verified_by')) {
                $table->dropForeign(['receipt_verified_by']);
            }
            if (Schema::hasColumn('petty_cash_transactions', 'disbursed_by')) {
                $table->dropForeign(['disbursed_by']);
            }
            
            $columns = ['receipt_status', 'receipt_verified_by', 'receipt_verified_at', 
                        'receipt_verification_notes', 'disbursed_by', 'disbursed_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('petty_cash_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
