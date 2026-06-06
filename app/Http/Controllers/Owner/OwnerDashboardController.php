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
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $totalEmployees = User::where('role', 'employee')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        
        $monthlyPresent = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->where('status', 'masuk')
                            ->where('approval_status', 'approved')
                            ->count();

        $monthlyLate = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->where('is_late', true)
                            ->count();

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $chartData[] = [
                'date' => $now->copy()->subDays($i)->format('d M'),
                'count' => Attendance::where('date', $date)->where('status', 'masuk')->count()
            ];
        }

        $admins = User::where('role', 'admin')->latest()->get();

        return view('owner.dashboard', compact(
            'totalEmployees', 
            'totalAdmins', 
            'monthlyPresent', 
            'monthlyLate', 
            'chartData',
            'admins'
        ));
    }
}