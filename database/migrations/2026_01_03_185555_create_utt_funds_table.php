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
        Schema::create('utt_funds', function (Blueprint $table) {
            $table->id();
            $table->string('fund_name');
            $table->string('fund_code')->unique();
            $table->string('currency', 3)->default('TZS');
            $table->enum('investment_horizon', ['SHORT-TERM', 'LONG-TERM'])->default('LONG-TERM');
            $table->decimal('expense_ratio', 5, 4)->nullable()->comment('Optional expense ratio as percentage');
            $table->enum('status', ['Active', 'Closed'])->default('Active');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('fund_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utt_funds');
    }
};
