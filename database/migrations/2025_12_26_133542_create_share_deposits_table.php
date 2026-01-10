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
        Schema::create('share_deposits', function (Blueprint $table) {
            $table->id();
            // Create column without foreign key constraint first (share_accounts table may not exist yet)
            $table->unsignedBigInteger('share_account_id');
            $table->date('deposit_date');
            $table->decimal('deposit_amount', 15, 2);
            $table->decimal('number_of_shares', 15, 4); // Can have decimal shares
            $table->decimal('charge_amount', 15, 2)->nullable(); // Charge amount if applicable
            $table->decimal('total_amount', 15, 2); // deposit_amount + charge_amount
            $table->string('transaction_reference')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('restrict');
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
            $table->index('deposit_date');
            $table->index('status');
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_deposits');
    }
};
