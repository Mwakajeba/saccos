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
        Schema::create('accrued_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('loan_schedule_id')->constrained('loan_schedules')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('penalty_amount', 15, 2);
            $table->date('accrual_date');
            $table->string('penalty_type')->default('percentage'); // 'percentage' or 'fixed'
            $table->decimal('penalty_rate', 10, 4)->nullable();
            $table->string('calculation_basis')->nullable(); // 'principal', 'interest', 'principal_and_interest', 'loan_amount'
            $table->integer('days_overdue')->default(0);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->boolean('posted_to_gl')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['loan_id', 'accrual_date']);
            $table->index(['loan_schedule_id', 'accrual_date']);
            $table->index(['customer_id', 'accrual_date']);
            $table->index('accrual_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrued_penalties');
    }
};
