<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user and first branch
        $userId = DB::table('users')->orderBy('id')->value('id');
        $branchId = DB::table('branches')->orderBy('id')->value('id');

        if ($userId && $branchId) {
            // Insert or update branch_user relationship (prevents duplicate entry errors)
            DB::table('branch_user')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'branch_id' => $branchId
                ],
                [
                    'user_id' => $userId,
                    'branch_id' => $branchId
                ]
            );
        }
    }
}
