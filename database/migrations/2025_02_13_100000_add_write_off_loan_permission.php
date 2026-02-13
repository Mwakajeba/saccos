<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exists = DB::table('permissions')
            ->where('name', 'write off loan')
            ->where('guard_name', 'web')
            ->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'write off loan',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'write off loan')
            ->where('guard_name', 'web')
            ->delete();
    }
};
