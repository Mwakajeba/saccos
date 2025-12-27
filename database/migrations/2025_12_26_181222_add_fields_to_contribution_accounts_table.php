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
        Schema::table('contribution_accounts', function (Blueprint $table) {
            $table->date('opening_date')->nullable()->after('account_number');
            $table->text('notes')->nullable()->after('opening_date');
            $table->decimal('balance', 15, 2)->default(0)->after('notes');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null')->after('balance');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null')->after('branch_id');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('company_id');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contribution_accounts', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['opening_date', 'notes', 'balance', 'branch_id', 'company_id', 'created_by', 'updated_by']);
        });
    }
};
