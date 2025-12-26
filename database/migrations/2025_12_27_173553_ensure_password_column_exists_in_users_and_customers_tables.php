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
        // Ensure password column exists in users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->after('sms_verified_at');
            });
        }

        // Ensure password column exists in customers table
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'password')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('password')->after('sex');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We don't drop the password column in down() to avoid data loss
        // If you need to remove it, create a separate migration
    }
};
