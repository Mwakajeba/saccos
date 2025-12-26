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
        Schema::create('share_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->constrained('share_accounts')->onDelete('restrict'); // Source account
            $table->foreignId('to_account_id')->constrained('share_accounts')->onDelete('restrict'); // Destination account
            $table->date('transfer_date');
            $table->decimal('number_of_shares', 15, 4); // Number of shares being transferred
            $table->decimal('transfer_amount', 15, 2); // transfer_amount = number_of_shares * nominal_price
            $table->decimal('transfer_fee', 15, 2)->nullable(); // Transfer fee if applicable
            $table->string('transaction_reference')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('restrict'); // For fee payment
            $table->foreignId('journal_reference_id')->nullable()->constrained('journal_references')->onDelete('restrict'); // For GL transactions
            $table->foreignId('fee_income_account_id')->nullable()->constrained('chart_accounts')->onDelete('restrict'); // For fee GL entry
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index('from_account_id');
            $table->index('to_account_id');
            $table->index('transfer_date');
            $table->index('status');
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_transfers');
    }
};
