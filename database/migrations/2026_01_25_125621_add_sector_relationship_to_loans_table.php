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
        Schema::table('loans', function (Blueprint $table) {
            // Add sector_id column
            $table->unsignedBigInteger('sector_id')->nullable()->after('sector');
            
            // Add foreign key constraint
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('set null');
        });

        // Migrate existing data: match sector names to sector IDs
        DB::statement("
            UPDATE loans 
            SET sector_id = (SELECT id FROM sectors WHERE sectors.name = loans.sector LIMIT 1)
            WHERE sector IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};
