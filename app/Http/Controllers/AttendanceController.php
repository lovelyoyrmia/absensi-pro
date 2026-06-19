<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::now()->setTimezone('Asia/Jakarta')->toDateString());

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
        $todayDate = $now->toDateString();
        $currentTime = $now->format('H:i');

        // ==========================================
        // 1. PROTEKSI STATUS CUTI / SAKIT / IZIN
        // ==========================================
        $today = Attendance::where('user_id', $user->id)
                        ->whereDate('date',$todayDate)
                        ->first();

        if ($today && in_array($today->status, ['sakit', 'izin', 'cuti'])) {
            return redirect()->route('dashboard')->with('error', 'Hari ini Anda ditandai sedang ' . strtoupper($today->status) . '. Tidak bisa melakukan absensi.');
        }

        $lat = $_COOKIE['user_lat'] ?? null;
        $lng = $_COOKIE['user_lng'] ?? null;

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

        // ==========================================
        // 2. PROSES CLOCK IN
        // ==========================================
        if (!$today) {
            $shiftName = 'Pagi';
            $startTime = '08:00';

            if ($user->division === 'CS') {
                // Ambil jadwal acak mingguan yang di-input Admin dari tabel 'employee_shifts'
                $schedule = DB::table('employee_shifts')
                    ->where('user_id', $user->id)
                    ->whereDate('date', $todayDate)
                    ->first();

                if ($schedule) {
                    $shiftName = $schedule->shift_name; // Shift 1, Shift 2, Shift 3
                    $startTime = $schedule->start_time; // Misal 07:00, 15:00, 23:00
                } else {
                    $startTime = '08:00'; 
                }
            }

            $isLate = $currentTime > $startTime;

            if ($isLate) {
                session([
                    'pending_attendance' => [
                        'shift_name'  => $shiftName,
                        'clock_in'    => $now->toDateTimeString(),
                        'address'     => $address,
                        'startTime'   => $startTime
                    ]
                ]);
                return redirect()->route('attendance.late-form')->with('info', 'Anda terlambat! Sila isi alasan dan lampirkan bukti.');
            }

            Attendance::create([
                'user_id'         => $user->id,
                'date'            => $todayDate,
                'shift_name'      => $shiftName,
                'clock_in'        => $now,
                'is_late'         => false,
                'address'         => $address,
                'status'          => 'masuk',
                'approval_status' => 'approved'
            ]);

            return redirect()->route('dashboard')->with('success', 'Berhasil Clock In tepat waktu!');
        }

        if (!$today->clock_out) {
            $clockInTime = Carbon::parse($today->clock_in);
            
            // Hitung total durasi kerja dalam hitungan menit
            $workDurationMinutes = $clockInTime->diffInMinutes($now);
            $minimumRequiredMinutes = 8 * 60; // 8 Jam = 480 Menit

            if ($workDurationMinutes < $minimumRequiredMinutes) {
                $minutesLeft = $minimumRequiredMinutes - $workDurationMinutes;
                $hoursLeft = floor($minutesLeft / 60);
                $remMinutes = $minutesLeft % 60;

                return redirect('/dashboard')->with('error', 'Gagal Clock Out! Durasi kerja belum memenuhi syarat wajib 8 jam. Sisa waktu kerja Anda: ' . $hoursLeft . ' jam ' . $remMinutes . ' menit lagi.');
            }

            $today->update([
                'clock_out' => $now
            ]);

            return redirect()->route('dashboard')->with('success', 'Berhasil Clock Out! Terima kasih atas kerja kerasnya hari ini.');
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

    public function storeLateReason(Request $request) {
        $request->validate([
            'late_reason' => 'required|string|max:500',
            'late_proof'  => 'required|image|mimes:jpg,jpeg,png|max:2048' // Maksimal file 2MB
        ]);

        $sessionData = session('pending_attendance');
        if (!$sessionData) {
            return redirect('/dashboard')->with('error', 'Sesi absensi kedaluwarsa. Silakan scan ulang.');
        }

        $path = $request->file('late_proof')->store('late_proofs', 'public');

        Attendance::create([
            'user_id'         => auth()->id(),
            'date'            => now()->setTimezone('Asia/Jakarta')->toDateString(),
            'shift_name'      => $sessionData['shift_name'],
            'clock_in'        => $sessionData['clock_in'],
            'is_late'         => true,
            'late_reason'     => $request->late_reason,
            'late_proof'      => $path,
            'address'         => $sessionData['address'],
            'status'          => 'masuk',
            'approval_status' => 'approved'
        ]);

        session()->forget('pending_attendance');

        return redirect()->route('dashboard')->with('success', 'Absensi terlambat berhasil disimpan dengan bukti!');
    }

    public function showLateForm()
    {
        $pendingAttendance = session('pending_attendance');

        if (!$pendingAttendance) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Tidak ada data absensi gantung yang perlu dilengkapi.');
        }

        return view('attendance.late-form');
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
