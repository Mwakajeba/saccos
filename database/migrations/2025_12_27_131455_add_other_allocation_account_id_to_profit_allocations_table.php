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
        Schema::table('profit_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('other_allocation_account_id')->nullable()->after('other_allocation_description');
            $table->foreign('other_allocation_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_allocations', function (Blueprint $table) {
            $table->dropForeign(['other_allocation_account_id']);
            $table->dropColumn('other_allocation_account_id');
        });
    }
};
