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
        Schema::create('utt_nav_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utt_fund_id')->constrained('utt_funds')->onDelete('cascade');
            $table->date('nav_date');
            $table->decimal('nav_per_unit', 15, 4);
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['utt_fund_id', 'nav_date'], 'unique_fund_nav_date');
            $table->index(['utt_fund_id', 'nav_date']);
            $table->index('nav_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utt_nav_prices');
    }
};
