<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());

        $attendances = Attendance::with('user')
            ->whereDate('date', $selectedDate)
            ->latest()
            ->get();

        return view('admin.attendance.index', compact('attendances', 'selectedDate'));
    }

    public function showScanner() {
        $inUrl = URL::temporarySignedRoute(
            'scan.process', 
            now()->addMinutes(1), 
            ['type' => 'in']
        );

        $outUrl = URL::temporarySignedRoute(
            'scan.process', 
            now()->addMinutes(1), 
            ['type' => 'out']
        );

        return view('admin.qr-display', compact('inUrl', 'outUrl'));
    }

    public function processScan(Request $request) {
        $user = auth()->user();
        $now = now();
        $today = Attendance::where('user_id', $user->id)
                       ->today()
                       ->first();
        $lat = $request->cookie('user_lat');
        $lng = $request->cookie('user_lng');

        $currentTime = now()->format('H:i');
        $limitTime = '20:00';

        $address = "Lokasi tidak ditemukan";
        if ($lat && $lng) {
            $response = Http::withHeaders(['User-Agent' => 'AttendanceApp'])
                ->get("https://nominatim.openstreetmap.org/reverse", [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                ]);

            if ($response->successful()) {
                $address = $response->json()['display_name'] ?? "Unknown Address";
            }
        }

        if (!$today) {
            
            $exists = Attendance::where('user_id', $user->id)
                ->today()
                ->whereNotNull('clock_in')
                ->exists();
            if ($exists) return redirect('/dashboard')->with('error', 'Sudah Clock In!');
            if ($currentTime > $limitTime) {
                return redirect()->route('dashboard')->with('error', 'Batas waktu absen berakhir jam 12:00.');
            }
            Attendance::create([
                'user_id' => $user->id,
                'date' => today(),
                'clock_in' => $now,
                'is_late' => $currentTime > '09:00',
                'address' => $address,
            ]);
            return redirect()->route('dashboard')->with('success', 'Berhasil Clock In!');
        }
        if (!$today->clock_out) {
            $today->update(['clock_out' => $now]);
            return redirect()->route('dashboard')->with('success', 'Berhasil Clock Out!');
        }
        
        return redirect()->route('dashboard')->with('info', 'Anda sudah menyelesaikan absensi hari ini.');
    }

    public function adminDashboard()
    {
        $attendanceUrl = URL::temporarySignedRoute(
            'attendance.scan', 
            now()->addHours(8), 
            ['action' => 'process']
        );

        $employees = User::where('role', 'employee')->get();
        $todayAttendances = Attendance::with('user')->whereDate('date', today())->latest()->get();

        return view('admin.dashboard', compact('attendanceUrl', 'employees', 'todayAttendances'));
    }
}
