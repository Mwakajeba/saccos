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
        Schema::table('share_accounts', function (Blueprint $table) {
            $table->string('certificate_number')->nullable()->after('account_number');
            $table->index('certificate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_accounts', function (Blueprint $table) {
            $table->dropIndex(['certificate_number']);
            $table->dropColumn('certificate_number');
        });
    }
};
