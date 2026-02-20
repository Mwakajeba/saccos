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
        Schema::table('loan_products', function (Blueprint $table) {
            $table->boolean('can_freeze_interest_accrual')->default(false)->after('maximum_number_of_loans');
            $table->integer('arrears_days_to_stop_interest_accrual')->nullable()->after('can_freeze_interest_accrual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn(['can_freeze_interest_accrual', 'arrears_days_to_stop_interest_accrual']);
        });
    }
};
