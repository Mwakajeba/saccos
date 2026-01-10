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
        Schema::create('utt_cash_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utt_fund_id')->constrained('utt_funds')->onDelete('cascade');
            $table->foreignId('utt_transaction_id')->nullable()->constrained('utt_transactions')->onDelete('set null');
            $table->enum('cash_flow_type', ['Subscription', 'Redemption', 'Income Distribution', 'Reinvestment'])->default('Subscription');
            $table->date('transaction_date');
            $table->decimal('amount', 15, 2);
            $table->enum('flow_direction', ['IN', 'OUT'])->default('OUT');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->enum('classification', ['Capital', 'Income'])->default('Capital');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['utt_fund_id', 'transaction_date']);
            $table->index(['cash_flow_type', 'transaction_date']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utt_cash_flows');
    }
};
