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
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'cash_collateral_id')) {
                $table->unsignedBigInteger('cash_collateral_id')->nullable();
                $table->foreign('cash_collateral_id')->references('id')->on('cash_collaterals')->onDelete('set null');
                $table->index('cash_collateral_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'cash_collateral_id')) {
                $table->dropForeign(['cash_collateral_id']);
                $table->dropIndex(['cash_collateral_id']);
                $table->dropColumn('cash_collateral_id');
            }
        });
    }
};
