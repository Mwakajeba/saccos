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
        Schema::create('share_products', function (Blueprint $table) {
            $table->id();
            $table->string('share_name');
            $table->decimal('required_share', 15, 2);
            $table->decimal('nominal_price', 15, 2);
            $table->integer('minimum_active_period');
            $table->string('minimum_active_period_type'); // Days, Weeks, Months, Years
            $table->boolean('allow_dividends_for_inactive_member')->default(false);
            $table->integer('lockin_period_frequency');
            $table->string('lockin_period_frequency_type'); // Days, Weeks, Months, Years
            $table->text('description')->nullable();
            $table->boolean('has_charges')->default(false);
            $table->foreignId('journal_reference_account_id')->constrained('chart_accounts')->onDelete('restrict'); // Journal reference (share transfer)
            $table->string('hrms_code')->nullable(); // Optional as per user requirement
            $table->foreignId('liability_account_id')->constrained('chart_accounts')->onDelete('restrict');
            $table->foreignId('income_account_id')->constrained('chart_accounts')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_products');
    }
};
