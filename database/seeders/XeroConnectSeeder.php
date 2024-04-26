<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Xero\XeroConnect;

class XeroConnectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        XeroConnect::create([
            'id_token' => '1',
            'access_token' => '1',
            'expires_in' => 1,
            'token_type' => '1',
            'refresh_token' => '1',
            'scope' => '1',
        ]);
    }
}
