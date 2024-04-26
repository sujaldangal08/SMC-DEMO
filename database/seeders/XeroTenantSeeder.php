<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Xero\XeroTenant;


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
