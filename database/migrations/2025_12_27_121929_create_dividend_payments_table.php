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
        Schema::create('dividend_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->unsignedBigInteger('dividend_id');
            $table->unsignedBigInteger('share_account_id');
            $table->unsignedBigInteger('customer_id');
            $table->decimal('member_shares', 15, 2);
            $table->decimal('dividend_amount', 15, 2);
            $table->enum('payment_method', ['cash', 'savings_deposit', 'convert_to_shares'])->default('cash');
            $table->unsignedBigInteger('savings_account_id')->nullable(); // If payment_method is savings_deposit
            $table->unsignedBigInteger('share_product_id')->nullable(); // If converting to shares
            $table->decimal('shares_converted', 15, 2)->nullable(); // If converting to shares
            $table->unsignedBigInteger('bank_account_id')->nullable(); // For cash payment
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'converted', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dividend_id')->references('id')->on('dividends')->onDelete('cascade');
            $table->foreign('share_account_id')->references('id')->on('share_accounts')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('savings_account_id')->references('id')->on('share_accounts')->nullable()->onDelete('set null'); // Assuming savings use share_accounts or create separate table
            $table->foreign('share_product_id')->references('id')->on('share_products')->onDelete('set null');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
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
        Schema::dropIfExists('dividend_payments');
    }
};
