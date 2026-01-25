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
        Schema::create('asset_maintenance_work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('wo_number')->unique();
            // $table->foreignId('maintenance_request_id')->nullable()->constrained('maintenance_requests')->onDelete('set null');
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            // $table->foreignId('maintenance_type_id')->nullable()->constrained('asset_maintenance_types')->onDelete('set null');
            $table->unsignedBigInteger('maintenance_type_id')->nullable();
            $table->string('maintenance_type')->nullable(); // preventive, corrective, predictive, etc.
            $table->string('execution_type')->nullable(); // internal, external, warranty
            $table->foreignId('vendor_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Dates
            $table->date('estimated_start_date')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            
            // Cost tracking
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('estimated_labor_cost', 15, 2)->default(0);
            $table->decimal('estimated_material_cost', 15, 2)->default(0);
            $table->decimal('estimated_other_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('actual_labor_cost', 15, 2)->default(0);
            $table->decimal('actual_material_cost', 15, 2)->default(0);
            $table->decimal('actual_other_cost', 15, 2)->default(0);
            
            // Downtime tracking
            $table->decimal('estimated_downtime_hours', 8, 2)->nullable();
            $table->decimal('actual_downtime_hours', 8, 2)->nullable();
            
            // Financial
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('budget_reference_id')->nullable();
            
            // Status and descriptions
            $table->string('status')->default('draft'); // draft, scheduled, in_progress, completed, cancelled, on_hold
            $table->text('work_description')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('technician_notes')->nullable();
            
            // Capital improvement tracking
            $table->string('cost_classification')->nullable(); // capital, expense
            $table->boolean('is_capital_improvement')->default(false);
            $table->decimal('capitalization_threshold', 15, 2)->nullable();
            $table->integer('life_extension_months')->nullable();
            
            // Approval workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            // GL integration
            $table->boolean('gl_posted')->default(false);
            $table->foreignId('gl_journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->timestamp('gl_posted_at')->nullable();
            
            // Additional fields
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('asset_id');
            $table->index('status');
            $table->index('wo_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_work_orders');
    }
};
