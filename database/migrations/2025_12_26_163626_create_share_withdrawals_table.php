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
        Schema::create('share_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_account_id')->constrained('share_accounts')->onDelete('restrict');
            $table->date('withdrawal_date');
            $table->decimal('withdrawal_amount', 15, 2); // Amount to be paid out
            $table->decimal('number_of_shares', 15, 4); // Number of shares being withdrawn
            $table->decimal('withdrawal_fee', 15, 2)->nullable(); // Withdrawal fee if applicable
            $table->decimal('total_amount', 15, 2); // withdrawal_amount - withdrawal_fee (net amount paid)
            $table->string('transaction_reference')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('restrict');
            $table->foreignId('liability_account_id')->nullable()->constrained('chart_accounts')->onDelete('restrict');
            $table->foreignId('share_capital_account_id')->nullable()->constrained('chart_accounts')->onDelete('restrict');
            $table->string('cheque_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index('share_account_id');
            $table->index('withdrawal_date');
            $table->index('status');
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_withdrawals');
    }
};
