<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->setTimezone('Asia/Jakarta')->toDateString());

        $employees = User::where('role', 'employee') 
            ->with(['attendances' => function($query) use ($selectedDate) {
                $query->whereDate('date', $selectedDate);
            }])
            ->get();

        return view('admin.attendance.index', compact('employees', 'selectedDate'));
    }

    public function showScanner() {
        $inUrl = URL::temporarySignedRoute(
            'scan.process', 
            now()->setTimezone('Asia/Jakarta')->addMinutes(1), 
            ['type' => 'in']
        );

        $outUrl = URL::temporarySignedRoute(
            'scan.process', 
            now()->setTimezone('Asia/Jakarta')->addMinutes(1), 
            ['type' => 'out']
        );

        return view('admin.qr-display', compact('inUrl', 'outUrl'));
    }

    public function processScan(Request $request) {
        $user = auth()->user();
        $now = now()->setTimezone('Asia/Jakarta');
        $today = Attendance::where('user_id', $user->id)
                       ->today()
                       ->first();
        $lat = $_COOKIE['user_lat'] ?? null;
        $lng = $_COOKIE['user_lng'] ?? null;

        $currentTime = $now->format('H:i');
        $settings = Setting::pluck('value', 'key')->all();
        $startTime = $settings['work_start_time'] ?? '08:00';
        $limitTime = $settings['work_limit_time'] ?? '17:00';

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
                return redirect()->route('dashboard')->with('error', 'Batas waktu absen berakhir jam ' . $limitTime . '.');
            }
            Attendance::create([
                'user_id' => $user->id,
                'date' => today()->setTimezone('Asia/Jakarta'),
                'clock_in' => $now,
                'is_late' => $currentTime > $startTime,
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

    public function storeIzin(Request $request)
    {
        $request->validate([
            'status' => 'required|in:sakit,izin,cuti',
            'notes' => 'required|string|max:500',
        ]);

        $user = auth()->user();
        $timezoneDate = now()->setTimezone('Asia/Jakarta');

        $exists = Attendance::where('user_id', $user->id)
            ->today()
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Anda sudah mengisi absensi atau izin hari ini!');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $timezoneDate->toDateString(),
            'clock_in' => $timezoneDate, // Samakan penanda waktu isi dokumen
            'clock_out' => $timezoneDate, // Langsung tutup agar tidak diminta clock out lagi
            'is_late' => false,
            'status' => $request->status,
            'notes' => $request->notes,
            'address' => 'Pengajuan Non-Kehadiran (' . ucfirst($request->status) . ')',
            'approval_status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Keterangan ' . ucfirst($request->status) . ' berhasil disimpan!');
    }

    public function adminDashboard()
    {
        $attendanceUrl = URL::temporarySignedRoute(
            'attendance.scan', 
            now()->setTimezone('Asia/Jakarta')->addHours(8), 
            ['action' => 'process']
        );

        $employees = User::where('role', 'employee')->get();
        $todayAttendances = Attendance::with('user')->whereDate('date', today()->setTimezone('Asia/Jakarta'))->latest()->get();
        $user = auth()->user();
        return view('admin.dashboard', compact('attendanceUrl', 'employees', 'todayAttendances', 'user'));
    }

    public function leavesIndex()
    {
        $submissions = Attendance::whereIn('status', ['sakit', 'izin', 'cuti'])
            ->where('approval_status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('admin.attendance.leaves', compact('submissions'));
    }

    public function approveLeave($id)
    {
        $attendance = Attendance::findOrFail($id);
        $now = now()->setTimezone('Asia/Jakarta');

        $attendance->update([
            'approval_status' => 'approved',
            'clock_in' => $now, 
            'clock_out' => $now, 
            'address' => 'Disetujui Admin: Pengajuan Non-Kehadiran (' . ucfirst($attendance->status) . ')'
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin karyawan berhasil disetujui!');
    }

    // 3. Aksi menolak pengajuan
    public function rejectLeave($id)
    {
        $attendance = Attendance::findOrFail($id);
        
        $attendance->update([
            'approval_status' => 'rejected',
            'address' => 'Ditolak oleh Admin'
        ]);

        return redirect()->back()->with('info', 'Pengajuan izin karyawan telah ditolak.');
    }
}
