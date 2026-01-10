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
        Schema::create('sacco_utt_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utt_fund_id')->constrained('utt_funds')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->decimal('total_units', 15, 4)->default(0);
            $table->decimal('average_acquisition_cost', 15, 4)->default(0)->comment('Average cost per unit');
            $table->date('last_reconciliation_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['utt_fund_id', 'company_id'], 'unique_fund_company');
            $table->index(['company_id', 'utt_fund_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacco_utt_holdings');
    }
};
