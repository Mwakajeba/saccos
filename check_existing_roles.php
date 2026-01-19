<?php

/**
 * Check Existing Roles and Their Permissions
 * 
 * Usage: php check_existing_roles.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "Checking All Roles and Their Permissions\n";
echo "=================================================\n\n";

try {
    $roles = Role::with('permissions')->get();
    
    if ($roles->count() === 0) {
        echo "⚠️  No roles found in database!\n";
        echo "Run: php artisan db:seed --class=RolePermissionSeeder\n\n";
        exit;
    }

    echo "Total Roles: {$roles->count()}\n";
    echo "Total Permissions: " . Permission::count() . "\n\n";

    echo "-------------------------------------------------\n";
    foreach ($roles as $role) {
        echo "\n📋 Role: {$role->name} (ID: {$role->id})\n";
        echo "   Description: " . ($role->description ?? 'N/A') . "\n";
        echo "   Guard: {$role->guard_name}\n";
        echo "   Created: {$role->created_at}\n";
        echo "   Updated: {$role->updated_at}\n";
        
        $permCount = $role->permissions->count();
        echo "   Permissions: {$permCount}\n";
        
        if ($permCount > 0) {
            echo "   \n";
            if ($permCount <= 10) {
                // Show all if 10 or less
                foreach ($role->permissions as $perm) {
                    echo "     ✓ {$perm->name}\n";
                }
            } else {
                // Show first 10 and count
                foreach ($role->permissions->take(10) as $perm) {
                    echo "     ✓ {$perm->name}\n";
                }
                echo "     ... and " . ($permCount - 10) . " more\n";
            }
        } else {
            echo "     ⚠️  No permissions assigned!\n";
        }
        
        // Check database directly
        $dbCount = DB::table('role_has_permissions')
            ->where('role_id', $role->id)
            ->count();
        
        if ($dbCount != $permCount) {
            echo "   ⚠️  WARNING: Mismatch detected!\n";
            echo "      Eloquent count: {$permCount}\n";
            echo "      Database count: {$dbCount}\n";
            echo "      This suggests a caching issue. Run: php artisan permission:cache-reset\n";
        }
        
        echo "-------------------------------------------------\n";
    }

    echo "\n";
    echo "Database Table Counts:\n";
    echo "  roles: " . DB::table('roles')->count() . "\n";
    echo "  permissions: " . DB::table('permissions')->count() . "\n";
    echo "  role_has_permissions: " . DB::table('role_has_permissions')->count() . "\n";
    echo "  model_has_roles: " . DB::table('model_has_roles')->count() . "\n";
    echo "\n";

    // Check for orphaned records
    echo "Checking for data integrity issues...\n";
    
    $orphanedPerms = DB::table('role_has_permissions as rhp')
        ->leftJoin('roles as r', 'rhp.role_id', '=', 'r.id')
        ->whereNull('r.id')
        ->count();
    
    if ($orphanedPerms > 0) {
        echo "  ⚠️  Found {$orphanedPerms} orphaned permission assignments (role deleted but permissions remain)\n";
    } else {
        echo "  ✓ No orphaned permission assignments\n";
    }
    
    echo "\n=================================================\n";
    echo "Check completed!\n";
    echo "=================================================\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
