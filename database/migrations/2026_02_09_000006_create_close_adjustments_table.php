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
        Schema::create('close_adjustments', function (Blueprint $table) {
            $table->id('adjustment_id');
            $table->unsignedBigInteger('close_id');
            $table->unsignedBigInteger('account_id');
            $table->enum('entry_type', ['DEBIT', 'CREDIT']);
            $table->decimal('amount', 20, 2);
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('posted_journal_id')->nullable();
            $table->timestamps();

            $table->foreign('close_id')->references('close_id')->on('close_batches')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_accounts')->onDelete('cascade');
            $table->index(['close_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('close_adjustments');
    }
};
