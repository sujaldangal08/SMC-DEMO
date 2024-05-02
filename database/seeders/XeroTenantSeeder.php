<?php

namespace Database\Seeders;

use App\Models\Xero\XeroTenant;
use Illuminate\Database\Seeder;

class XeroTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        XeroTenant::create([
            'connection_id' => '1',
            'authEventId' => '1',
            'tenantId' => '1',
            'tenantType' => '1',
            'tenantName' => '1',
            'xero_connect_id' => 1, // Replace with your XeroConnect id
            'createdDateUtc' => now(),
            'updatedDateUtc' => now(),
        ]);
    }
}
