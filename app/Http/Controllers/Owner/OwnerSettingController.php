<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class OwnerSettingController extends Controller
{
    public function index()
    {
        // Ambil setelan dengan memetakan key menjadi value array
        $settings = Setting::pluck('value', 'key')->all();
        return view('owner.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_limit_time' => 'required|date_format:H:i',
        ]);

        Setting::where('key', 'work_start_time')->update(['value' => $request->work_start_time]);
        Setting::where('key', 'work_limit_time')->update(['value' => $request->work_limit_time]);

        return redirect()->back()->with('success', 'Konfigurasi parameter aturan kerja berhasil diperbarui!');
    }
}