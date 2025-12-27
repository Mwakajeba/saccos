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
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->string('dividend_number')->unique();
            $table->unsignedBigInteger('profit_allocation_id')->nullable();
            $table->unsignedBigInteger('share_product_id');
            $table->date('declaration_date');
            $table->date('payment_date')->nullable();
            $table->year('financial_year');
            $table->decimal('total_dividend_amount', 15, 2);
            $table->decimal('dividend_rate', 8, 4)->nullable(); // Percentage
            $table->string('calculation_method')->default('on_share_capital'); // on_share_capital, on_share_value, on_minimum_balance, on_average_balance
            $table->decimal('total_shares', 15, 2)->default(0);
            $table->decimal('dividend_per_share', 15, 4)->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('profit_allocation_id')->references('id')->on('profit_allocations')->onDelete('set null');
            $table->foreign('share_product_id')->references('id')->on('share_products')->onDelete('cascade');
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
        Schema::dropIfExists('dividends');
    }
};
