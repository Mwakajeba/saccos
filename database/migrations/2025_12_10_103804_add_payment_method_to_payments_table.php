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
            // Add payment method field
            $table->enum('payment_method', ['bank_transfer', 'cash_collateral', 'cheque'])->nullable()->after('bank_account_id');
            
            // Add cash collateral reference
            $table->string('cash_collateral_id')->nullable()->after('payment_method');

            // Add cheque reference
            $table->foreignId('cheque_id')->nullable()->after('cash_collateral_id')->constrained('cheques')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['cheque_id']);
            $table->dropColumn(['payment_method', 'cash_collateral_id', 'cheque_id']);
        });
    }
};
