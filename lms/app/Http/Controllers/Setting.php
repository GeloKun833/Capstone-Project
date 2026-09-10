<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = SchoolSetting::getSettings();
        return view('setting.settings', compact('settings'));
    }

    /**
     * Update school settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'website_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico|max:1024',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'zip_postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $settings = SchoolSetting::getSettings();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                    Storage::disk('public')->delete($settings->logo);
                }
                
                $logoPath = $request->file('logo')->store('settings/logos', 'public');
                $settings->logo = $logoPath;
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                // Delete old favicon if exists
                if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                    Storage::disk('public')->delete($settings->favicon);
                }
                
                $faviconPath = $request->file('favicon')->store('settings/favicons', 'public');
                $settings->favicon = $faviconPath;
            }

            // Update other settings
            $settings->website_name = $request->website_name;
            $settings->rtl_enabled = $request->has('rtl_enabled');
            $settings->address_line_1 = $request->address_line_1;
            $settings->address_line_2 = $request->address_line_2;
            $settings->city = $request->city;
            $settings->state_province = $request->state_province;
            $settings->zip_postal_code = $request->zip_postal_code;
            $settings->country = $request->country;
            $settings->phone = $request->phone;
            $settings->email = $request->email;
            $settings->description = $request->description;

            $settings->save();

            Cache::forget('school.contact.cards');

            Toastr::success('Settings updated successfully!', 'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error('Failed to update settings: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Delete uploaded file (logo or favicon)
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_type' => 'required|in:logo,favicon'
        ]);

        try {
            $settings = SchoolSetting::getSettings();
            $fileType = $request->file_type;

            if ($fileType === 'logo' && $settings->logo) {
                if (Storage::disk('public')->exists($settings->logo)) {
                    Storage::disk('public')->delete($settings->logo);
                }
                $settings->logo = null;
            } elseif ($fileType === 'favicon' && $settings->favicon) {
                if (Storage::disk('public')->exists($settings->favicon)) {
                    Storage::disk('public')->delete($settings->favicon);
                }
                $settings->favicon = null;
            }

            $settings->save();

            return response()->json(['success' => true, 'message' => ucfirst($fileType) . ' deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete file: ' . $e->getMessage()]);
        }
    }
}
