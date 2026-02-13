<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->string('status', 20)->default('posted')->after('writeoff_type');
        });
    }

    public function down(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
