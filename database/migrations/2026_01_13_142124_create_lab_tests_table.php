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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_number')->unique();
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade'); // Requesting doctor
            $table->string('test_name');
            $table->text('test_description')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->text('instructions')->nullable();
            $table->enum('status', [
                'pending_review',      // Created by doctor, waiting for lab review
                'bill_created',        // Lab reviewed and created bill
                'pending_payment',     // Bill sent to cashier, waiting payment
                'paid',                // Payment completed
                'test_taken',          // Lab has taken sample from patient
                'results_submitted',   // Lab has submitted results
                'results_sent_to_doctor', // Results sent back to doctor
                'cancelled'            // Test cancelled
            ])->default('pending_review');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Lab staff who reviewed
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('test_taken_by')->nullable()->constrained('users')->onDelete('set null'); // Lab staff who took test
            $table->timestamp('test_taken_at')->nullable();
            $table->foreignId('results_submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('results_submitted_at')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['consultation_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
