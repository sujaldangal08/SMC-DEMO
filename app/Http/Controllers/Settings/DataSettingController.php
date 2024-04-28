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
                    if (!is_bool($value)) {
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

        $message = 'Settings updated successfully';
        if ($updatedXeroSettings && $updatedForce2fa) {
            $message = 'Xero and force 2FA settings updated successfully';
        } else if ($updatedXeroSettings) {
            $message = 'Xero settings updated successfully';
        } else if ($updatedForce2fa) {
            $message = 'Force 2FA setting updated successfully';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => null,
        ]);
    }
}
