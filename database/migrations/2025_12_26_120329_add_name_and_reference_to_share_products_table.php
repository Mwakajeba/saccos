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
<<<<<<<< HEAD:database/migrations/2025_12_26_120329_add_name_and_reference_to_share_products_table.php
        Schema::table('share_products', function (Blueprint $table) {
            //
        });
========
        // Check if column already exists (it does in create_customers_table migration)
        if (!Schema::hasColumn('customers', 'password')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('password')->nullable();
            });
        }
>>>>>>>> a191c4e (Update composer.json to allow Symfony runtime plugin, modify migration to check for existing password column, enhance MenuSeeder with Shares Management routes, and add Shares Management routes in web.php.):database/migrations/2025_12_15_160511_add_password_to_customers_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            //
        });
    }
};
