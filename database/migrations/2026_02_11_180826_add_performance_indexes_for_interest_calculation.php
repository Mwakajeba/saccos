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
        // Add index on daily_interest_accruals.loan_id
        // Note: Foreign key may already create an index, but we ensure it exists
        Schema::table('daily_interest_accruals', function (Blueprint $table) {
            // Only add if it doesn't exist (foreign keys create indexes automatically)
            if (!$this->hasIndex('daily_interest_accruals', 'daily_interest_accruals_loan_id_index')) {
                $table->index('loan_id', 'daily_interest_accruals_loan_id_index');
            }
            
            // Add index on accrual_date for date-based queries
            if (!$this->hasIndex('daily_interest_accruals', 'daily_interest_accruals_accrual_date_index')) {
                $table->index('accrual_date', 'daily_interest_accruals_accrual_date_index');
            }
        });

        // Add index on loans.status for filtering active loans
        Schema::table('loans', function (Blueprint $table) {
            if (!$this->hasIndex('loans', 'loans_status_index')) {
                $table->index('status', 'loans_status_index');
            }
        });

        // Add index on loan_schedules.loan_id
        // Note: Foreign key may already create an index, but we ensure it exists
        Schema::table('loan_schedules', function (Blueprint $table) {
            if (!$this->hasIndex('loan_schedules', 'loan_schedules_loan_id_index')) {
                $table->index('loan_id', 'loan_schedules_loan_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_interest_accruals', function (Blueprint $table) {
            $table->dropIndex('daily_interest_accruals_loan_id_index');
            $table->dropIndex('daily_interest_accruals_accrual_date_index');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('loans_status_index');
        });

        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->dropIndex('loan_schedules_loan_id_index');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        try {
            $result = DB::select(
                "SELECT COUNT(*) as count 
                 FROM information_schema.statistics 
                 WHERE table_schema = ? 
                 AND table_name = ? 
                 AND index_name = ?",
                [$databaseName, $table, $indexName]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist and try to create it
            return false;
        }
    }
};
