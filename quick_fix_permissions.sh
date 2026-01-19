#!/bin/bash

# Quick Fix Script for Role Permissions Issues
# Usage: bash quick_fix_permissions.sh

echo "================================================="
echo "Role Permissions Quick Fix Script"
echo "================================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Change to project directory
cd /home/julius-mwakajeba/saccos/saccos

echo -e "${YELLOW}Step 1: Clearing all caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 2: Checking database connection...${NC}"
php artisan db:show || {
    echo -e "${RED}✗ Database connection failed${NC}"
    echo "Please check your .env database settings"
    exit 1
}
echo -e "${GREEN}✓ Database connected${NC}"
echo ""

echo -e "${YELLOW}Step 3: Checking required tables...${NC}"
php artisan tinker --execute="
if (!Schema::hasTable('roles')) {
    echo 'ERROR: roles table missing\n';
    exit(1);
}
if (!Schema::hasTable('permissions')) {
    echo 'ERROR: permissions table missing\n';
    exit(1);
}
if (!Schema::hasTable('role_has_permissions')) {
    echo 'ERROR: role_has_permissions table missing\n';
    exit(1);
}
echo 'All required tables exist\n';
" || {
    echo -e "${RED}✗ Required tables missing${NC}"
    echo "Run: php artisan migrate"
    exit 1
}
echo -e "${GREEN}✓ All required tables exist${NC}"
echo ""

echo -e "${YELLOW}Step 4: Checking for permissions...${NC}"
PERM_COUNT=$(php artisan tinker --execute="echo Permission::count();")
if [ "$PERM_COUNT" -eq "0" ]; then
    echo -e "${YELLOW}⚠ No permissions found. Running seeder...${NC}"
    php artisan db:seed --class=RolePermissionSeeder
    echo -e "${GREEN}✓ Permissions seeded${NC}"
else
    echo -e "${GREEN}✓ Found $PERM_COUNT permissions${NC}"
fi
echo ""

echo -e "${YELLOW}Step 5: Checking current roles and permissions...${NC}"
php check_existing_roles.php
echo ""

echo -e "${YELLOW}Step 6: Testing permission system...${NC}"
php test_role_permissions.php
echo ""

echo -e "${YELLOW}Step 7: Optimizing application...${NC}"
php artisan optimize
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

echo "================================================="
echo -e "${GREEN}Quick fix completed!${NC}"
echo "================================================="
echo ""
echo "Next steps:"
echo "1. Clear your browser cache (Ctrl+Shift+R)"
echo "2. Open browser console (F12)"
echo "3. Go to /roles page"
echo "4. Try creating a new role with permissions"
echo "5. Check browser console and Laravel logs"
echo ""
echo "To watch logs in real-time:"
echo "  tail -f storage/logs/laravel.log"
echo ""
