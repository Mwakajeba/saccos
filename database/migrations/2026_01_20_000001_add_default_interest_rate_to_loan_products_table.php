<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // One-decimal default interest rate used by the mobile app as a suggested rate.
            $table->decimal('default_interest_rate', 18, 1)
                ->nullable()
                ->after('maximum_interest_rate');
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn('default_interest_rate');
        });
    }
};

