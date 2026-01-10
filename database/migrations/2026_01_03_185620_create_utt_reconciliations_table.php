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
        Schema::create('utt_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utt_fund_id')->constrained('utt_funds')->onDelete('cascade');
            $table->foreignId('sacco_utt_holding_id')->constrained('sacco_utt_holdings')->onDelete('cascade');
            $table->date('reconciliation_date');
            $table->decimal('statement_units', 15, 4)->comment('Units from UTT statement');
            $table->decimal('system_units', 15, 4)->comment('Units in system');
            $table->decimal('variance', 15, 4)->default(0)->comment('Difference between statement and system');
            $table->enum('status', ['Draft', 'In Progress', 'Completed', 'Variance Identified'])->default('Draft');
            $table->text('variance_notes')->nullable();
            $table->text('reconciliation_notes')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['utt_fund_id', 'reconciliation_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utt_reconciliations');
    }
};
