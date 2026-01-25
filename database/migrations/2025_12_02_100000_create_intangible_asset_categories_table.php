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
        Schema::create('intangible_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->nullable(); // patent, copyright, trademark, software, license, etc.
            $table->boolean('is_goodwill')->default(false);
            $table->boolean('is_indefinite_life')->default(false);
            
            // GL Account mappings
            $table->unsignedBigInteger('cost_account_id')->nullable();
            $table->unsignedBigInteger('accumulated_amortisation_account_id')->nullable();
            $table->unsignedBigInteger('accumulated_impairment_account_id')->nullable();
            $table->unsignedBigInteger('amortisation_expense_account_id')->nullable();
            $table->unsignedBigInteger('impairment_loss_account_id')->nullable();
            $table->unsignedBigInteger('disposal_gain_loss_account_id')->nullable();
            
            $table->json('settings')->nullable();
            $table->timestamps();
            
            // Foreign keys with custom names to avoid length limit
            $table->foreign('cost_account_id', 'intang_cat_cost_acct_fk')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('accumulated_amortisation_account_id', 'intang_cat_accum_amort_fk')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('accumulated_impairment_account_id', 'intang_cat_accum_impair_fk')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('amortisation_expense_account_id', 'intang_cat_amort_exp_fk')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('impairment_loss_account_id', 'intang_cat_impair_loss_fk')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('disposal_gain_loss_account_id', 'intang_cat_disp_gl_fk')->references('id')->on('chart_accounts')->onDelete('set null');
            
            // Indexes
            $table->index('company_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intangible_asset_categories');
    }
};
