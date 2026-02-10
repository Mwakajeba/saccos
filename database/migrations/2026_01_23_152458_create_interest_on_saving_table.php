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
        if (Schema::hasTable('interest_on_saving')) {
            return; // Table already exists, skip migration
        }

        Schema::create('interest_on_saving', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_account_id')->constrained('contribution_accounts')->onDelete('cascade');
            $table->foreignId('contribution_product_id')->constrained('contribution_products')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('calculation_date'); // Date interest was calculated
            $table->date('date_of_calculation'); // Day of calculation
            $table->decimal('interest_rate', 8, 2); // Rate at time of calculation
            $table->decimal('interest_amount_gained', 15, 2); // Calculated interest amount
            $table->decimal('account_balance_at_interest_calculation', 15, 2); // Balance used
            $table->decimal('withholding_percentage', 5, 2)->nullable();
            $table->decimal('withholding_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // After deducting withholding
            $table->text('description')->nullable();
            $table->boolean('posted')->default(false);
            $table->string('reason')->nullable(); // Reason if not posted (e.g., "waiting for approval")
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('contribution_account_id');
            $table->index('calculation_date');
            $table->index('posted');
            $table->index(['contribution_account_id', 'calculation_date']); // For duplicate prevention
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_on_saving');
    }
};
