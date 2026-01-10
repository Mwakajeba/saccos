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
        // Add foreign key constraints after share_accounts table is created
        if (!Schema::hasTable('share_accounts')) {
            return; // share_accounts table doesn't exist yet
        }

        // Helper function to check if foreign key exists
        $hasForeignKey = function($tableName, $columnName) {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName, $columnName]);
            return !empty($foreignKeys);
        };

        // Add foreign key for share_deposits
        if (Schema::hasTable('share_deposits') && !$hasForeignKey('share_deposits', 'share_account_id')) {
            Schema::table('share_deposits', function (Blueprint $table) {
                $table->foreign('share_account_id')
                    ->references('id')
                    ->on('share_accounts')
                    ->onDelete('restrict');
            });
        }

        // Add foreign key for share_withdrawals
        if (Schema::hasTable('share_withdrawals') && !$hasForeignKey('share_withdrawals', 'share_account_id')) {
            Schema::table('share_withdrawals', function (Blueprint $table) {
                $table->foreign('share_account_id')
                    ->references('id')
                    ->on('share_accounts')
                    ->onDelete('restrict');
            });
        }

        // Add foreign keys for share_transfers
        if (Schema::hasTable('share_transfers')) {
            if (!$hasForeignKey('share_transfers', 'from_account_id')) {
                Schema::table('share_transfers', function (Blueprint $table) {
                    $table->foreign('from_account_id')
                        ->references('id')
                        ->on('share_accounts')
                        ->onDelete('restrict');
                });
            }
            if (!$hasForeignKey('share_transfers', 'to_account_id')) {
                Schema::table('share_transfers', function (Blueprint $table) {
                    $table->foreign('to_account_id')
                        ->references('id')
                        ->on('share_accounts')
                        ->onDelete('restrict');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys for share_deposits
        if (Schema::hasTable('share_deposits')) {
            Schema::table('share_deposits', function (Blueprint $table) {
                $table->dropForeign(['share_account_id']);
            });
        }

        // Drop foreign keys for share_withdrawals
        if (Schema::hasTable('share_withdrawals')) {
            Schema::table('share_withdrawals', function (Blueprint $table) {
                $table->dropForeign(['share_account_id']);
            });
        }

        // Drop foreign keys for share_transfers
        if (Schema::hasTable('share_transfers')) {
            Schema::table('share_transfers', function (Blueprint $table) {
                $table->dropForeign(['from_account_id']);
                $table->dropForeign(['to_account_id']);
            });
        }
    }
};
