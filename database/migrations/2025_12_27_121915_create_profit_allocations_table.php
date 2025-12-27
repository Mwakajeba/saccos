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
        Schema::create('profit_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('allocation_date');
            $table->year('financial_year');
            $table->decimal('total_profit', 15, 2);
            $table->decimal('statutory_reserve_percentage', 5, 2)->default(30.00);
            $table->decimal('statutory_reserve_amount', 15, 2)->default(0);
            $table->decimal('education_fund_percentage', 5, 2)->default(0);
            $table->decimal('education_fund_amount', 15, 2)->default(0);
            $table->decimal('community_fund_percentage', 5, 2)->default(0);
            $table->decimal('community_fund_amount', 15, 2)->default(0);
            $table->decimal('dividend_percentage', 5, 2)->default(0);
            $table->decimal('dividend_amount', 15, 2)->default(0);
            $table->decimal('other_allocation_percentage', 5, 2)->default(0);
            $table->decimal('other_allocation_amount', 15, 2)->default(0);
            $table->string('other_allocation_description')->nullable();
            $table->unsignedBigInteger('statutory_reserve_account_id')->nullable();
            $table->unsignedBigInteger('education_fund_account_id')->nullable();
            $table->unsignedBigInteger('community_fund_account_id')->nullable();
            $table->unsignedBigInteger('dividend_payable_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'approved', 'posted'])->default('draft');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('statutory_reserve_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('education_fund_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('community_fund_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('dividend_payable_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_allocations');
    }
};
