<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $data = $request->except(['_token', '_method']);
        foreach ($data as $key => $value) {
            HomeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        return redirect()->back()->with('success', 'Contenido de la home actualizado correctamente.');
    }
}
