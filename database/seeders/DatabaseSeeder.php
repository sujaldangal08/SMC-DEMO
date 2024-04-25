<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(BackendSeeder::class);
        $this->call(EmailSeeder::class);
        $this->call(AssetSeeder::class);
        $this->call(InsuranceSeeder::class);
        $this->call(MaintenanceSeeder::class);
        $this->call(DeliverySeeder::class);
        $this->call(DeliveryScheduleSeeder::class);
        $this->call(DeliveryTripSeeder::class);
        $this->call(RouteSeeder::class);
        $this->call(PickupScheduleSeeder::class);
        $this->call(SkuSeeder::class);
        $this->call(WarehouseSeeder::class);
        $this->call(InventorySeeder::class);
        $this->call(TicketSeeder::class);
        $this->call(WasteSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(SettingSeeder::class);
    }
}
