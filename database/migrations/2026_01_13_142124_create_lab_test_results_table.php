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
        Schema::create('lab_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->text('results')->nullable(); // JSON or text field for test results
            $table->text('findings')->nullable();
            $table->text('interpretation')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('result_file')->nullable(); // PDF or document file path
            $table->enum('status', ['draft', 'submitted', 'sent_to_doctor', 'viewed_by_doctor'])->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null'); // Lab staff
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('sent_to_doctor_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sent_to_doctor_at')->nullable();
            $table->foreignId('viewed_by_doctor')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('viewed_at')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['lab_test_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_results');
    }
};
