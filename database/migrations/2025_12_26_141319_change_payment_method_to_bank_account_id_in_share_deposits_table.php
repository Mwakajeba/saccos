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
            // Check if bank_account_id already exists
            if (Schema::hasColumn('share_deposits', 'bank_account_id')) {
                // Column already exists, just ensure foreign key constraint exists
                if (!$this->foreignKeyExists('share_deposits', 'share_deposits_bank_account_id_foreign')) {
                    $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('restrict');
                }
                return;
            }

            // Drop the old payment_method column if it exists
            if (Schema::hasColumn('share_deposits', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            
            // Add bank_account_id as foreign key
            $table->foreignId('bank_account_id')->nullable()->after('transaction_reference')->constrained('bank_accounts')->onDelete('restrict');
        });
    }

    /**
     * Check if a foreign key constraint exists
     */
    private function foreignKeyExists($table, $constraintName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = $connection->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ? 
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $constraintName]
        );
        
        return $result !== null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_deposits', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
            
            // Restore payment_method column
            $table->string('payment_method')->nullable()->after('transaction_reference');
        });
    }
};
