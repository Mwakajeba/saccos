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
        Schema::table('accrued_penalties', function (Blueprint $table) {
            $table->timestamp('reversed_at')->nullable()->after('user_id');
            $table->foreignId('reversal_journal_id')->nullable()->after('reversed_at')->constrained('journals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accrued_penalties', function (Blueprint $table) {
            $table->dropForeign(['reversal_journal_id']);
            $table->dropColumn(['reversed_at', 'reversal_journal_id']);
        });
    }
};
