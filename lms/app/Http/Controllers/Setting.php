<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Services\GradeSubjectCatalogService;
use App\Services\SystemAccessLimitService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
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
     * Limited access / load-control settings (Admin only).
     */
    public function accessLimits(SystemAccessLimitService $accessLimits)
    {
        $settings = SchoolSetting::getSettings();
        $grades = GradeSubjectCatalogService::gradeLevels();
        $preview = $accessLimits->preview();

        return view('setting.access-limits', compact('settings', 'grades', 'preview'));
    }

    /**
     * Save limited access / load-control settings.
     */
    public function updateAccessLimits(Request $request, SystemAccessLimitService $accessLimits)
    {
        $request->validate([
            'access_limits_enabled' => 'nullable|boolean',
            'max_teachers' => 'nullable|integer|min:0|max:5000',
            'max_students_per_grade' => 'nullable|integer|min:0|max:5000',
            'max_parents_per_grade' => 'nullable|integer|min:0|max:5000',
            'access_allowed_grades' => 'nullable|array',
            'access_allowed_grades.*' => 'string',
            'access_limits_message' => 'nullable|string|max:1000',
        ]);

        try {
            $settings = SchoolSetting::getSettings();
            $settings->access_limits_enabled = $request->boolean('access_limits_enabled');

            // Empty string from form => null (unlimited). "0" => block all of that role.
            $settings->max_teachers = $this->nullableQuota($request->input('max_teachers'));
            $settings->max_students_per_grade = $this->nullableQuota($request->input('max_students_per_grade'));
            $settings->max_parents_per_grade = $this->nullableQuota($request->input('max_parents_per_grade'));

            $selectedGrades = $request->input('access_allowed_grades', []);
            $validGrades = array_values(array_intersect(
                GradeSubjectCatalogService::gradeLevels(),
                is_array($selectedGrades) ? $selectedGrades : []
            ));
            $settings->access_allowed_grades = !empty($validGrades) ? $validGrades : null;
            $settings->access_limits_message = $request->input('access_limits_message');
            $settings->save();

            $accessLimits->clearCache();

            Toastr::success(
                $settings->access_limits_enabled
                    ? 'Limited access mode is ON. Only the configured quotas can use the system.'
                    : 'Limited access mode is OFF. All active users can use the system.',
                'Success'
            );

            return redirect()->route('setting.access-limits');
        } catch (\Exception $e) {
            Toastr::error('Failed to update access limits: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    protected function nullableQuota($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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
