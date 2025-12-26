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
        if (Schema::hasTable('contribution_products')) {
            return; // Table already exists, skip migration
        }

        Schema::create('contribution_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->decimal('interest', 8, 2);
            $table->enum('category', ['Voluntary', 'Mandatory']);
            $table->enum('auto_create', ['Yes', 'No']);
            $table->enum('compound_period', ['Daily', 'Monthly']);
            $table->enum('interest_posting_period', ['Monthly', 'Quarterly', 'Annually'])->nullable();
            $table->enum('interest_calculation_type', ['Daily', 'Monthly', 'Annually']);
            $table->integer('lockin_period_frequency');
            $table->enum('lockin_period_frequency_type', ['Days', 'Months']);
            $table->decimal('automatic_opening_balance', 15, 2)->default(0);
            $table->decimal('minimum_balance_for_interest_calculations', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('can_withdraw')->default(false);
            
            // Charges
            $table->boolean('has_charge')->default(false);
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->enum('charge_type', ['Fixed', 'Percentage'])->nullable();
            $table->decimal('charge_amount', 15, 2)->nullable();
            
            // Bank Account & Journal Reference
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->unsignedBigInteger('journal_reference_id')->nullable(); // contribution transfer
            $table->unsignedBigInteger('riba_journal_id')->nullable(); // Journal ya riba juu ya contribution
            $table->unsignedBigInteger('pay_loan_journal_id')->nullable(); // Journal ya kulipa mkopo kwa contribution
            
            // Chart Accounts
            $table->unsignedBigInteger('liability_account_id')->nullable();
            $table->unsignedBigInteger('expense_account_id')->nullable();
            $table->unsignedBigInteger('riba_payable_account_id')->nullable();
            $table->unsignedBigInteger('withholding_account_id')->nullable();
            $table->decimal('withholding_percentage', 5, 2)->nullable();
            $table->unsignedBigInteger('riba_payable_journal_id')->nullable();
            
            // Company and Branch
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('bank_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('journal_reference_id')->references('id')->on('journal_references')->onDelete('set null');
            $table->foreign('riba_journal_id')->references('id')->on('journal_references')->onDelete('set null');
            $table->foreign('pay_loan_journal_id')->references('id')->on('journal_references')->onDelete('set null');
            $table->foreign('liability_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('expense_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('riba_payable_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('withholding_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('riba_payable_journal_id')->references('id')->on('journal_references')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribution_products');
    }
};
