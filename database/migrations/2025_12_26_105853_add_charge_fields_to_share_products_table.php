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
        Schema::table('share_products', function (Blueprint $table) {
            $table->foreignId('charge_id')->nullable()->constrained('fees')->onDelete('set null')->after('has_charges');
            $table->enum('charge_type', ['fixed', 'percentage'])->nullable()->after('charge_id');
            $table->decimal('charge_amount', 15, 2)->nullable()->after('charge_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_products', function (Blueprint $table) {
            $table->dropForeign(['charge_id']);
            $table->dropColumn(['charge_id', 'charge_type', 'charge_amount']);
        });
    }
};
