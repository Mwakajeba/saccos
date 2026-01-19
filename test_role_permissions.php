<?php

/**
 * Test Script: Verify Role Permissions Are Being Saved
 * 
 * Usage: php test_role_permissions.php
 * 
 * This script will:
 * 1. Create a test role
 * 2. Assign some permissions to it
 * 3. Verify the permissions were saved
 * 4. Display the results
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "Testing Role Permission Assignment\n";
echo "=================================================\n\n";

try {
    DB::beginTransaction();

    // 1. Create a test role
    echo "1. Creating test role...\n";
    $testRole = Role::create([
        'name' => 'test-role-' . time(),
        'description' => 'Test role for debugging permissions',
        'guard_name' => 'web'
    ]);
    echo "   ✓ Role created: ID={$testRole->id}, Name={$testRole->name}\n\n";

    // 2. Get some permissions to assign
    echo "2. Getting permissions from database...\n";
    $permissions = Permission::take(5)->get();
    echo "   ✓ Found {$permissions->count()} permissions\n";
    foreach ($permissions as $perm) {
        echo "     - {$perm->name} (ID: {$perm->id})\n";
    }
    echo "\n";

    // 3. Assign permissions to role
    echo "3. Assigning permissions to role...\n";
    $testRole->syncPermissions($permissions);
    echo "   ✓ Permissions synced\n\n";

    // 4. Verify permissions were saved
    echo "4. Verifying permissions in database...\n";
    
    // Check using Spatie's method
    $testRole->load('permissions');
    $assignedPermissions = $testRole->permissions;
    echo "   Method 1 (Eloquent): {$assignedPermissions->count()} permissions found\n";
    
    // Check directly in database
    $dbCount = DB::table('role_has_permissions')
        ->where('role_id', $testRole->id)
        ->count();
    echo "   Method 2 (Database): {$dbCount} records in role_has_permissions\n\n";

    if ($assignedPermissions->count() > 0) {
        echo "5. Assigned permissions:\n";
        foreach ($assignedPermissions as $perm) {
            echo "   ✓ {$perm->name} (ID: {$perm->id})\n";
        }
        echo "\n";
        echo "✅ SUCCESS: Permissions are being saved correctly!\n\n";
    } else {
        echo "❌ FAILURE: No permissions were saved!\n\n";
        echo "Debugging info:\n";
        echo "- Role ID: {$testRole->id}\n";
        echo "- Role Name: {$testRole->name}\n";
        echo "- Permissions table count: " . Permission::count() . "\n";
        echo "- role_has_permissions table count: " . DB::table('role_has_permissions')->count() . "\n";
    }

    // 6. Check if role can check permissions
    echo "6. Testing hasPermissionTo() method:\n";
    foreach ($permissions->take(3) as $perm) {
        $hasIt = $testRole->hasPermissionTo($perm->name);
        $icon = $hasIt ? '✓' : '✗';
        echo "   {$icon} hasPermissionTo('{$perm->name}'): " . ($hasIt ? 'YES' : 'NO') . "\n";
    }
    echo "\n";

    // Clean up - rollback to not affect database
    DB::rollBack();
    echo "7. Cleanup: Test role and permissions rolled back (not saved)\n\n";

    echo "=================================================\n";
    echo "Test completed successfully!\n";
    echo "=================================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
