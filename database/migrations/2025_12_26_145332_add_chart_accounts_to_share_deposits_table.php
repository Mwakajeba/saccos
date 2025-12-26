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
        Schema::table('share_deposits', function (Blueprint $table) {
            $table->foreignId('liability_account_id')->nullable()->after('bank_account_id')->constrained('chart_accounts')->onDelete('restrict');
            $table->foreignId('share_capital_account_id')->nullable()->after('liability_account_id')->constrained('chart_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_deposits', function (Blueprint $table) {
            $table->dropForeign(['liability_account_id']);
            $table->dropForeign(['share_capital_account_id']);
            $table->dropColumn(['liability_account_id', 'share_capital_account_id']);
        });
    }
};
