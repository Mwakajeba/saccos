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
        Schema::create('close_batches', function (Blueprint $table) {
            $table->id('close_id');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->unsignedBigInteger('period_id');
            $table->string('batch_label');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('prepared_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['DRAFT', 'REVIEW', 'APPROVED', 'LOCKED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('period_id')->references('period_id')->on('accounting_periods')->onDelete('cascade');
            $table->index(['company_id', 'status']);
            $table->index(['period_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('close_batches');
    }
};
