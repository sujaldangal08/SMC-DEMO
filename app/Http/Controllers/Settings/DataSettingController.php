<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
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
    public function updateSettingValue(Request $request, $id)
    {
        $setting = Setting::where('id', $id)->first();
        if ($setting) {
            $value = $request->setting_value;
            if ($id == 1 || $id == 2) {
                $value = Crypt::encryptString($value);
            }
            $setting->update(['setting_value' => $value]);
            return response()->json([
                'status' => 'success',
                'message' => 'Setting updated successfully',
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
}
