<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    // Helper internal untuk mengekstrak tanggal berdasarkan filter Hari, Minggu, Bulan
    private function getFilterRange(Request $request)
    {
        $type = $request->query('type', 'day'); // day, week, month
        $targetDate = $request->query('date', now()->toDateString());
        $carbonDate = Carbon::parse($targetDate);

        if ($type === 'week') {
            $start = $carbonDate->copy()->startOfWeek()->toDateString();
            $end = $carbonDate->copy()->endOfWeek()->toDateString();
            $label = "Minggu ke-" . $carbonDate->weekOfMonth . " (" . Carbon::parse($start)->format('d M') . " - " . Carbon::parse($end)->format('d M Y') . ")";
        } elseif ($type === 'month') {
            $start = $carbonDate->copy()->startOfMonth()->toDateString();
            $end = $carbonDate->copy()->endOfMonth()->toDateString();
            $label = "Bulan " . $carbonDate->format('F Y');
        } else {
            $start = $targetDate;
            $end = $targetDate;
            $label = $carbonDate->format('d M Y');
        }

        return [$start, $end, $type, $targetDate, $label];
    }

    // 1. LAPORAN STAFF (Satu folder per nama karyawan)
    public function staffReport(Request $request)
    {
        [$start, $end, $type, $targetDate, $label] = $this->getFilterRange($request);
        
        $employees = User::where('role', 'employee')->get();
        $selectedEmployeeId = $request->query('employee_id');

        $attendances = collect();
        $selectedEmployee = null;

        if ($selectedEmployeeId) {
            $selectedEmployee = User::findOrFail($selectedEmployeeId);
            $attendances = Attendance::where('user_id', $selectedEmployeeId)
                ->whereBetween('date', [$start, $end])
                ->orderBy('date', 'asc')
                ->get();
        }

        return view('admin.reports.staff', compact(
            'employees', 'attendances', 'selectedEmployee', 
            'type', 'targetDate', 'label', 'selectedEmployeeId'
        ));
    }

    // 2. LAPORAN KEHADIRAN (Rekap kumulatif untuk perhitungan potongan)
    public function attendanceReport(Request $request)
    {
        [$start, $end, $type, $targetDate, $label] = $this->getFilterRange($request);

        $employees = User::where('role', 'employee')->get();
        $reportData = [];

        $totalWorkDays = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;

        foreach ($employees as $emp) {
            $records = Attendance::where('user_id', $emp->id)
                ->whereBetween('date', [$start, $end])
                ->get();

            $hadir = $records->where('status', 'masuk')->count();
            $telat = $records->where('is_late', true)->count();
            $cuti  = $records->where('status', 'cuti')->count();
            $izin  = $records->where('status', 'izin')->count();
            $sakit = $records->where('status', 'sakit')->count();
            
            $alpha = $totalWorkDays - ($hadir + $cuti + $izin + $sakit);
            if ($alpha < 0) $alpha = 0;

            $potonganTelat = $telat * 50000;   // Rp 50.000 per telat
            $potonganAlpha = $alpha * 100000; // Rp 100.000 per alpha
            $totalPotongan = $potonganTelat + $potonganAlpha;

            $reportData[] = [
                'name' => $emp->name,
                'nip' => $emp->nip,
                'division' => $emp->division,
                'hadir' => $hadir,
                'telat' => $telat,
                'cuti' => $cuti,
                'izin' => $izin + $sakit,
                'alpha' => $alpha,
                'potongan' => $totalPotongan
            ];
        }

        return view('admin.reports.attendance', compact('reportData', 'type', 'targetDate', 'label'));
    }
}