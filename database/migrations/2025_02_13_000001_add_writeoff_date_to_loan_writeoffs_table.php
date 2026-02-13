<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->date('writeoff_date')->after('writeoff_type')->default(now()->toDateString());
        });

        // Backfill existing records with created_at date (for tables that already have data)
        DB::table('loan_writeoffs')->update([
            'writeoff_date' => DB::raw('DATE(created_at)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->dropColumn('writeoff_date');
        });
    }
};
