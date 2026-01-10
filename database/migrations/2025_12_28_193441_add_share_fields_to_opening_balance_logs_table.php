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
        Schema::table('opening_balance_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('opening_balance_logs', 'type')) {
                $table->enum('type', ['contribution', 'share'])->default('contribution')->after('id');
            }
            if (!Schema::hasColumn('opening_balance_logs', 'share_account_id')) {
                $table->unsignedBigInteger('share_account_id')->nullable()->after('contribution_account_id');
            }
            if (!Schema::hasColumn('opening_balance_logs', 'share_product_id')) {
                $table->unsignedBigInteger('share_product_id')->nullable()->after('contribution_product_id');
            }
            if (!Schema::hasColumn('opening_balance_logs', 'share_deposit_id')) {
                $table->unsignedBigInteger('share_deposit_id')->nullable()->after('journal_id');
            }
        });
        
        // Add foreign keys separately to avoid issues
        Schema::table('opening_balance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('opening_balance_logs', 'share_account_id') && !$this->foreignKeyExists('opening_balance_logs', 'opening_balance_logs_share_account_id_foreign')) {
                $table->foreign('share_account_id')->references('id')->on('share_accounts')->onDelete('set null');
            }
            if (Schema::hasColumn('opening_balance_logs', 'share_product_id') && !$this->foreignKeyExists('opening_balance_logs', 'opening_balance_logs_share_product_id_foreign')) {
                $table->foreign('share_product_id')->references('id')->on('share_products')->onDelete('set null');
            }
            if (Schema::hasColumn('opening_balance_logs', 'share_deposit_id') && !$this->foreignKeyExists('opening_balance_logs', 'opening_balance_logs_share_deposit_id_foreign')) {
                $table->foreign('share_deposit_id')->references('id')->on('share_deposits')->onDelete('set null');
            }
        });
    }
    
    private function foreignKeyExists($table, $keyName)
    {
        try {
            $connection = Schema::getConnection();
            $database = $connection->getDatabaseName();
            $foreignKeys = DB::select(
                "SELECT CONSTRAINT_NAME 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = ? 
                 AND TABLE_NAME = ? 
                 AND CONSTRAINT_NAME = ?",
                [$database, $table, $keyName]
            );
            return count($foreignKeys) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opening_balance_logs', function (Blueprint $table) {
            //
        });
    }
};
