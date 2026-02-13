<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_writeoff_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_writeoff_id');
            $table->integer('approval_level');
            $table->string('approver_type')->nullable();
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_name');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('loan_writeoff_id')->references('id')->on('loan_writeoffs')->onDelete('cascade');
            $table->index(['loan_writeoff_id', 'approval_level', 'status'], 'lwo_approvals_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_writeoff_approvals');
    }
};
