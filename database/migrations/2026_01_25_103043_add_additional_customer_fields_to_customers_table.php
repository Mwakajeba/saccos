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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('marital_status')->nullable()->after('sex');
            $table->string('email')->nullable()->after('phone2');
            $table->string('employment_status')->nullable()->after('work');
            $table->text('street')->nullable()->after('district_id');
            $table->integer('number_of_spouse')->nullable()->default(0)->after('relation');
            $table->integer('number_of_children')->nullable()->default(0)->after('number_of_spouse');
            $table->decimal('monthly_income', 15, 2)->nullable()->after('description');
            $table->decimal('monthly_expenses', 15, 2)->nullable()->after('monthly_income');
            $table->string('bank_name')->nullable()->after('monthly_expenses');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'marital_status',
                'email',
                'employment_status',
                'street',
                'number_of_spouse',
                'number_of_children',
                'monthly_income',
                'monthly_expenses',
                'bank_name',
                'bank_account',
                'bank_account_name',
            ]);
        });
    }
};
