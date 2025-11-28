<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\HomeSetting;

class HomeSettingsController extends Controller
{
    public function edit()
    {
        $settings = HomeSetting::all()->keyBy('key');
        return view('admin.home_settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        // Validate file uploads
        $validator = Validator::make($request->all(), [
            'slider_bg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                // Handle file upload
                $file = $request->file($key);
                if ($file->isValid()) {
                    // Delete old file if exists
                    $existingSetting = HomeSetting::where('key', $key)->first();
                    if ($existingSetting && $existingSetting->value && file_exists(public_path('storage/' . $existingSetting->value))) {
                        unlink(public_path('storage/' . $existingSetting->value));
                    }
                    // Store new file
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('storage/home_settings'), $filename);
                    $value = 'home_settings/' . $filename;
                }
            }

            HomeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        return redirect()->back()->with('success', 'Contenido de la home actualizado correctamente.');
    }
}
