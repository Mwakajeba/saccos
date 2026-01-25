<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    $this->call([
      CompanySeeder::class,
      BranchSeeder::class,
      DepartmentSeeder::class,
      RolePermissionSeeder::class,
      UserSeeder::class,
      PermissionGroupsSeeder::class,
      MenuSeeder::class,
      AccountClassSeeder::class,
      MainGroupSeeder::class,
      AccountClassGroupSeeder::class,
      ChartAccountSeeder::class,
      PayrollChartAccountSeeder::class,
      OpeningBalanceAccountsSeeder::class,
      JournalReferenceSeeder::class,
      CashCollateralTypeSeeder::class,
      EquityCategorySeeder::class,
      CurrencySeeder::class,
      SystemSettingSeeder::class,
      RegionsTableSeeder::class,
      DistrictsTableSeeder::class,
      SupplierSeeder::class,
      FeeSeeder::class,
      FiletypeSeeder::class,
      BranchUserSeeder::class,
      GroupSeeder::class,
      TanzaniaPublicHolidaySeeder::class,
      LeaveTypeSeeder::class,
      SalaryComponentSeeder::class,
      //payroll seeder
      StatutoryRuleSeeder::class,
      // Create default one-year subscriptions for all companies
      DefaultSubscriptionSeeder::class,
    ]);
  }
}
