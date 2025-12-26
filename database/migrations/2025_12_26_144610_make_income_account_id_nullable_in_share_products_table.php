<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['income_account_id']);
        });

        // Modify the column to be nullable
        DB::statement('ALTER TABLE `share_products` MODIFY `income_account_id` BIGINT UNSIGNED NULL');

        // Re-add the foreign key constraint
        Schema::table('share_products', function (Blueprint $table) {
            $table->foreign('income_account_id')->references('id')->on('chart_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['income_account_id']);
        });

        // Modify the column to be NOT NULL (you may need to set default values for existing NULL records)
        DB::statement('ALTER TABLE `share_products` MODIFY `income_account_id` BIGINT UNSIGNED NOT NULL');

        // Re-add the foreign key constraint
        Schema::table('share_products', function (Blueprint $table) {
            $table->foreign('income_account_id')->references('id')->on('chart_accounts')->onDelete('restrict');
        });
    }
};
