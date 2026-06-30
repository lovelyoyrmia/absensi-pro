<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        $todayDate = $now->toDateString();
        
        // Batas awal dan akhir bulan ini untuk kalkulasi makro
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();
        
        // Batas awal dan akhir minggu ini
        $startOfWeek = $now->copy()->startOfWeek()->toDateString();
        $endOfWeek = $now->copy()->endOfWeek()->toDateString();

        // 1. Perhitungan Jumlah Struktur Organisasi
        $totalEmployees = User::where('role', 'employee')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // 2. Perhitungan Kehadiran Terdistribusi (Harian, Mingguan, Bulanan)
        $presentToday = Attendance::whereDate('date', $todayDate)
            ->where('status', 'masuk')
            ->count();

        $presentThisWeek = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', 'masuk')
            ->count();

        $presentThisMonth = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'masuk')
            ->count();

        // 3. Menghitung Kasus Keterlambatan Global (Kunci Error Anda)
        $globalLateCount = Attendance::where('is_late', true)->count();
        
        // Menghitung jumlah telat khusus bulan ini (untuk kartu stats)
        $monthlyLate = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('is_late', true)
            ->count();

        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $lateAttendancesThisMonth = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('is_late', true)
            ->get()
            ->groupBy('user_id');

        $globalDeductionTotal = 0;

        foreach ($lateAttendancesThisMonth as $userId => $attendances) {
            $totalLateCount = $attendances->count();
            
            if ($totalLateCount >= 3) {
                $chargeableLate = $totalLateCount - 2; 
                $globalDeductionTotal += ($chargeableLate * 50000);
            }
        }

        // 5. Data Grafik Sederhana (7 Hari Terakhir)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $chartData[] = [
                'date' => $now->copy()->subDays($i)->format('d M'),
                'count' => Attendance::whereDate('date', $date)->where('status', 'masuk')->count()
            ];
        }

        // 6. Daftar Admin Aktif untuk Side-Widget Panel
        $admins = User::where('role', 'admin')->latest()->get();

        // Mengirimkan SEMUA variabel ke dalam view blade owner
        return view('owner.dashboard', compact(
            'totalEmployees', 
            'totalAdmins', 
            'presentToday',
            'presentThisWeek',
            'presentThisMonth',
            'globalLateCount', 
            'monthlyLate',
            'globalDeductionTotal',
            'chartData',
            'admins'
        ));
    }
}