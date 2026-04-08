<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function processScan($type) {
        $user = auth()->user();
        $now = now();
        $workStart = now()->setHour(9)->setMinute(0);

        if ($type === 'in') {
            // Prevent double clock-in
            $exists = Attendance::where('user_id', $user->id)
                ->today()
                ->whereNotNull('clock_in')
                ->exists();
            if ($exists) return redirect('/dashboard')->with('error', 'Already clocked in today!');

            Attendance::create([
                'user_id' => $user->id,
                'date' => today(),
                'clock_in' => $now,
                'is_late' => $now->gt($workStart)
            ]);
        } else {
            $record = Attendance::where('user_id', $user->id)->today()->whereNull('clock_out')->first();
            if ($record) $record->update(['clock_out' => $now]);
        }

        return redirect('/dashboard')->with('success', 'Success! Status: ' . ($now->gt($workStart) ? 'LATE' : 'ON TIME'));
    }

    public function adminDashboard()
    {
        $employees = User::where('role', 'employee')->get();

        $todayAttendances = Attendance::with('user')
            ->whereDate('date', today())
            ->latest()
            ->get();

        $inUrl = URL::temporarySignedRoute('scan.process', now()->addMinutes(1), ['type' => 'in']);
        $outUrl = URL::temporarySignedRoute('scan.process', now()->addMinutes(1), ['type' => 'out']);

        return view('admin.dashboard', compact('employees', 'todayAttendances', 'inUrl', 'outUrl'));
    }
}
