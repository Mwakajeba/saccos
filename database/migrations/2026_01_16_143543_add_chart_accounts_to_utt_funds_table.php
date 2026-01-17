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
        Schema::table('utt_funds', function (Blueprint $table) {
            $table->foreignId('investment_account_id')->nullable()->after('notes')->constrained('chart_accounts')->onDelete('set null')->comment('UTT Investment Asset Account');
            $table->foreignId('income_account_id')->nullable()->after('investment_account_id')->constrained('chart_accounts')->onDelete('set null')->comment('UTT Investment Income Account');
            $table->foreignId('loss_account_id')->nullable()->after('income_account_id')->constrained('chart_accounts')->onDelete('set null')->comment('UTT Investment Loss Expense Account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utt_funds', function (Blueprint $table) {
            $table->dropForeign(['investment_account_id']);
            $table->dropForeign(['income_account_id']);
            $table->dropForeign(['loss_account_id']);
            $table->dropColumn(['investment_account_id', 'income_account_id', 'loss_account_id']);
        });
    }
};
