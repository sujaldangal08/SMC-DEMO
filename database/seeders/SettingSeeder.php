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
            ['setting_name' => 'smtp_host', 'setting_value' => null],
            ['setting_name' => 'smtp_username', 'setting_value' => null],
            ['setting_name' => 'smtp_password', 'setting_value' => null],
            ['setting_name' => 'smtp_port', 'setting_value' => null],
            ['setting_name' => 'smtp_protocol', 'setting_value' => null],
            ['setting_name' => 'whatsapp_client_key', 'setting_value' => null],
            ['setting_name' => 'whatsapp_secret_key', 'setting_value' => null],
            ['setting_name' => 'google_location_key', 'setting_value' => null],
            ['setting_name' => 'recaptcha_client_key', 'setting_value' => null],
            ['setting_name' => 'recaptcha_client_secret', 'setting_value' => null],
            ['setting_name' => 'google_ocr_key', 'setting_value' => null],

        ]);
    }
}
