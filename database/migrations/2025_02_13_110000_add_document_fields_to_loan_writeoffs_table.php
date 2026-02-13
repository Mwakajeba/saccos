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
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->string('policy_reference')->nullable()->after('reason');
            $table->string('external_reference')->nullable()->after('policy_reference');
            $table->string('document_path')->nullable()->after('external_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->dropColumn(['policy_reference', 'external_reference', 'document_path']);
        });
    }
};
