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
        Schema::create('utt_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utt_fund_id')->constrained('utt_funds')->onDelete('cascade');
            $table->foreignId('sacco_utt_holding_id')->constrained('sacco_utt_holdings')->onDelete('cascade');
            $table->enum('transaction_type', ['BUY', 'SELL', 'REINVESTMENT'])->default('BUY');
            $table->date('trade_date');
            $table->date('nav_date');
            $table->date('settlement_date');
            $table->decimal('units', 15, 4);
            $table->decimal('nav_per_unit', 15, 4);
            $table->decimal('total_cash_value', 15, 2);
            $table->enum('status', ['PENDING', 'APPROVED', 'SETTLED', 'CANCELLED'])->default('PENDING');
            $table->string('reference_number')->unique()->nullable();
            $table->text('description')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('maker_id')->nullable()->constrained('users')->onDelete('set null')->comment('User who created the transaction');
            $table->foreignId('checker_id')->nullable()->constrained('users')->onDelete('set null')->comment('User who approved the transaction');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['utt_fund_id', 'trade_date']);
            $table->index(['status', 'trade_date']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utt_transactions');
    }
};
