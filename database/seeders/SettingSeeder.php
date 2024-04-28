<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::insert([
            ['setting_name' => 'xero_client_id', 'setting_value' => null],
            ['setting_name' => 'xero_client_secret', 'setting_value' => null],
            ['setting_name' => 'callback_url', 'setting_value' => null],
            ['setting_name' => 'force_2fa_enable', 'setting_value' => false],
        ]);
    }
}
