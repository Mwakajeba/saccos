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
        if (!Schema::hasTable('contribution_products')) {
            return;
        }

        Schema::table('contribution_products', function (Blueprint $table) {
            if (!Schema::hasColumn('contribution_products', 'has_interest')) {
                $table->boolean('has_interest_on_saving')->default(true)->after('interest');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('contribution_products')) {
            return;
        }

        Schema::table('contribution_products', function (Blueprint $table) {
            if (Schema::hasColumn('contribution_products', 'has_interest')) {
                $table->dropColumn('has_interest');
            }
        });
    }
};
