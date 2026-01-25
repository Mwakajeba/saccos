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
        Schema::create('intangible_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('category_id')->constrained('intangible_asset_categories')->onDelete('restrict');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('source_type')->nullable(); // purchased, internally_developed, acquired_in_business_combination
            $table->date('acquisition_date');
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('accumulated_amortisation', 15, 2)->default(0);
            $table->decimal('accumulated_impairment', 15, 2)->default(0);
            $table->decimal('nbv', 15, 2)->default(0);
            $table->integer('useful_life_months')->nullable();
            $table->boolean('is_indefinite_life')->default(false);
            $table->boolean('is_goodwill')->default(false);
            $table->string('status')->default('active'); // active, fully_amortised, impaired, disposed
            $table->text('description')->nullable();
            $table->json('recognition_checks')->nullable(); // identifiability, control, future_benefits
            $table->foreignId('initial_journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intangible_assets');
    }
};
