<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->foreignId('reversal_of_id')->nullable()->after('status')->constrained('loan_writeoffs')->nullOnDelete();
            $table->foreignId('reversed_by_id')->nullable()->after('reversal_of_id')->constrained('loan_writeoffs')->nullOnDelete();
            $table->string('previous_loan_status', 50)->nullable()->after('reversed_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('loan_writeoffs', function (Blueprint $table) {
            $table->dropForeign(['reversal_of_id']);
            $table->dropForeign(['reversed_by_id']);
            $table->dropColumn(['reversal_of_id', 'reversed_by_id', 'previous_loan_status']);
        });
    }
};
