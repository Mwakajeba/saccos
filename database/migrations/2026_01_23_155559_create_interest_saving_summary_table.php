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
        if (Schema::hasTable('interest_saving_summary')) {
            return; // Table already exists, skip migration
        }

        Schema::create('interest_saving_summary', function (Blueprint $table) {
            $table->id();
            $table->date('calculation_date'); // Date interest was calculated
            $table->string('day_of_calculation'); // Day name (Monday, Tuesday, etc.)
            $table->integer('total_accounts')->default(0); // Total number of accounts processed
            $table->integer('total_customers')->default(0); // Total number of unique customers
            $table->decimal('total_interest_amount', 15, 2)->default(0); // Sum of all interest amounts
            $table->decimal('total_withholding_amount', 15, 2)->default(0); // Sum of all withholding amounts
            $table->decimal('total_net_amount', 15, 2)->default(0); // Sum of all net amounts
            $table->decimal('total_balance', 15, 2)->default(0); // Sum of all account balances
            $table->integer('processed_count')->default(0); // Number of accounts processed successfully
            $table->integer('skipped_count')->default(0); // Number of accounts skipped
            $table->integer('error_count')->default(0); // Number of errors
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('calculation_date');
            $table->index(['branch_id', 'company_id', 'calculation_date'], 'iss_branch_company_date_idx');
            // Unique constraint: one summary per date per branch/company
            $table->unique(['calculation_date', 'branch_id', 'company_id'], 'iss_unique_date_branch_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_saving_summary');
    }
};
