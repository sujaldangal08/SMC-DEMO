<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DataSettingController extends Controller
{
    public function getAll()
    {
        $settings = Setting::all();
        foreach ($settings as $setting) {
            if (isset($setting->setting_value) && Crypt::decryptString($setting->setting_value, $unserialize = false)) {
                $setting->setting_value = Crypt::decryptString($setting->setting_value);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings found',
            'data' => $settings,
        ]);
    }

    public function getOne($id)
    {
        $setting = Setting::find($id);
        $setting->setting_value = Crypt::decryptString($setting->setting_value);
        if ($setting) {
            return response()->json([
                'status' => 'success',
                'message' => 'Setting found',
                'data' => $setting,
            ]);
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Setting not found',
                'data' => null,
            ], 404);
        }
    }

    // public function insertSetting(Request $request)
    // {
    //     $encryptedValue = Crypt::encryptString($request->setting_value);

    //     $setting = Setting::updateOrCreate(
    //         ['setting_name' => $request->setting_name],
    //         ['setting_value' => $encryptedValue]
    //     );

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Setting updated successfully',
    //         'data' => $setting,
    //     ]);
    // }
    public function updateSettingValue(Request $request)
    {
        $settings = $request->settings;
        $updatedSettings = [];
        $updatedXeroSettings = false;
        $updatedForce2fa = false;

        foreach ($settings as $settingData) {
            $setting = Setting::find($settingData['id']);

            if ($setting) {
                $value = $settingData['setting_value'];

                if ($setting->setting_name == 'force_2fa_enable') {
                    if (! is_bool($value)) {
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'Invalid value for force_2fa_enable. It should be either true or false.',
                            'data' => null,
                        ], 400);
                    } else {
                        $updatedForce2fa = true;
                    }
                }

                if ($setting->id == 1 || $setting->id == 2) {
                    $value = Crypt::encryptString($value);
                    $updatedXeroSettings = true;
                }
                $encryptSettings = [
                    'smtp_password',
                    'recaptcha_client_key',
                    'recaptcha_client_secret',
                    'whatsapp_secret_key',
                    'whatsapp_client_key',
                    'google_location_key',
                    'google_ocr_key'
                ];

                if (in_array($setting->setting_name, $encryptSettings)) {
                    $value = Crypt::encryptString($value);
                }

                $setting->update(['setting_value' => $value]);
                $updatedSettings[] = $setting;
            }
        }

        if (empty($updatedSettings)) {
            return response()->json([
                'status' => 'failure',
                'message' => 'No valid settings found',
                'data' => null,
            ], 404);
        }
        $settingMessages = [
            'smtp_host' => 'SMTP host',
            'smtp_username' => 'SMTP username',
            'smtp_port' => 'SMTP port',
            'smtp_protocol' => 'SMTP protocol',
            'smtp_password' => 'SMTP password',
            'recaptcha_client_key' => 'Recaptcha client key',
            'recaptcha_client_secret' => 'Recaptcha client secret',
            'whatsapp_secret_key' => 'WhatsApp secret key',
            'whatsapp_client_key' => 'WhatsApp client key',
            'google_location_key' => 'Google location key',
            'google_ocr_key' => 'Google OCR key',
        ];

        $updatedSettings = [];
        if (isset($settingMessages[$setting->setting_name])) {
            $updatedSettings[] = $settingMessages[$setting->setting_name];
        }

        if ($updatedXeroSettings) {
            $updatedSettings[] = 'Xero';
        }

        if ($updatedForce2fa) {
            $updatedSettings[] = 'Force 2FA';
        }


        $message = implode(' and ', $updatedSettings) . ' settings updated successfully';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => null,
        ]);
    }
}
