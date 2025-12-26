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
        // Check if the column already exists with the new name
        if (Schema::hasColumn('share_products', 'journal_reference_id')) {
            return; // Migration already applied
        }

        // Check if the old column exists
        if (!Schema::hasColumn('share_products', 'journal_reference_account_id')) {
            // Neither column exists, skip migration
            return;
        }

        Schema::table('share_products', function (Blueprint $table) {
            // Try to drop the old foreign key if it exists
            try {
                $table->dropForeign(['journal_reference_account_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist or has different name, continue
            }
        });
        
        // Set existing values to NULL since they reference chart_accounts, not journal_references
        DB::table('share_products')->update(['journal_reference_account_id' => null]);
        
        // Use raw SQL to rename column and make it nullable temporarily
        DB::statement('ALTER TABLE `share_products` CHANGE `journal_reference_account_id` `journal_reference_id` BIGINT UNSIGNED NULL');
        
        Schema::table('share_products', function (Blueprint $table) {
            // Add new foreign key to journal_references table (nullable for now)
            $table->foreign('journal_reference_id')->references('id')->on('journal_references')->onDelete('restrict');
        });
        
        // Make it NOT NULL after adding foreign key (since it's required in the form)
        DB::statement('ALTER TABLE `share_products` MODIFY `journal_reference_id` BIGINT UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['journal_reference_id']);
        });
        
        // Set values to NULL before renaming back
        DB::table('share_products')->update(['journal_reference_id' => null]);
        
        // Use raw SQL to rename column back
        DB::statement('ALTER TABLE `share_products` CHANGE `journal_reference_id` `journal_reference_account_id` BIGINT UNSIGNED NOT NULL');
        
        Schema::table('share_products', function (Blueprint $table) {
            // Restore old foreign key
            $table->foreign('journal_reference_account_id')->references('id')->on('chart_accounts')->onDelete('restrict');
        });
    }
};
