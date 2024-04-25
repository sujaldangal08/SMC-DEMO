<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        $settings = $this->decryptSettings($settings);
        return response()->json(['settings' => $settings]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'setting_name' => 'required',
            'setting_value' => 'required',
        ]);

        $encryptedValue = encrypt($request->input('setting_value'));
        $setting = Setting::create([
            'setting_name' => $request->input('setting_name'),
            'setting_value' => $encryptedValue,
        ]);

        return response()->json(['message' => 'Setting created successfully', 'setting' => $setting]);
    }

    public function show($id)
    {
        $setting = Setting::findOrFail($id);
        $setting = $this->decryptSetting($setting);
        return response()->json(['setting' => $setting]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'setting_value' => 'required',
        ]);

        $setting = Setting::findOrFail($id);
        $encryptedValue = encrypt($request->input('setting_value'));
        $setting->setting_value = $encryptedValue;
        $setting->save();

        return response()->json(['message' => 'Setting updated successfully', 'setting' => $setting]);
    }

    // public function destroy($id)
    // {
    //     $setting = Setting::findOrFail($id);
    //     $setting->delete();
    //     return response()->json(['message' => 'Setting deleted successfully']);
    // }

    // Helper function to decrypt a single setting
    private function decryptSetting($setting)
    {
        $setting->setting_value = decrypt($setting->setting_value);
        return $setting;
    }

    // Helper function to decrypt multiple settings
    private function decryptSettings($settings)
    {
        foreach ($settings as $setting) {
            $setting->setting_value = decrypt($setting->setting_value);
        }
        return $settings;
    }
}
