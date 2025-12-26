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
        Schema::table('share_products', function (Blueprint $table) {
            $table->foreignId('fee_income_account_id')->nullable()->after('share_capital_account_id')->constrained('chart_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            $table->dropForeign(['fee_income_account_id']);
            $table->dropColumn('fee_income_account_id');
        });
    }
};
