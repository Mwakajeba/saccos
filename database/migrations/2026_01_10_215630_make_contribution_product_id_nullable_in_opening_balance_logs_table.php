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
        Schema::table('opening_balance_logs', function (Blueprint $table) {
            // Make contribution_product_id nullable since it's only needed for contribution type logs
            // Share type logs don't need this field
            $table->unsignedBigInteger('contribution_product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This cannot be safely reversed if there are NULL values
        // You would need to set default values or delete records with NULL
        Schema::table('opening_balance_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('contribution_product_id')->nullable(false)->change();
        });
    }
};
