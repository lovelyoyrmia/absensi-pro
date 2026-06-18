<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminShiftController extends Controller
{
    public function index(Request $request)
    {
        // Ambil daftar karyawan khusus divisi CS
        $employees = User::where('role', 'employee')->where('division', 'CS')->get();

        // Ambil acuan tanggal pencarian (default hari ini)
        $selectedDate = $request->query('date', now()->toDateString());

        // Ambil semua jadwal shift yang terdaftar pada tanggal terpilih
        $shifts = EmployeeShift::with('user')
            ->whereDate('date', $selectedDate)
            ->latest()
            ->get();

        return view('admin.shifts.index', compact('employees', 'shifts', 'selectedDate'));
    }

    // 2. Menyimpan plot jadwal shift baru
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'shift_name' => 'required|in:Shift 1,Shift 2,Shift 3',
        ]);

        // Tentukan template jam kerja otomatis berdasarkan pilihan Shift
        $startTime = '07:00';
        $endTime = '15:00';

        if ($request->shift_name === 'Shift 2') {
            $startTime = '15:00';
            $endTime = '23:00';
        } elseif ($request->shift_name === 'Shift 3') {
            $startTime = '23:00';
            $endTime = '07:00'; // Selesai keesokan harinya
        }

        // Simpan data atau update jika data user_id dan tanggal yang sama sudah ada (Upsert)
        EmployeeShift::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'date' => $request->date,
            ],
            [
                'shift_name' => $request->shift_name,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]
        );

        return redirect()->back()->with('success', 'Jadwal kerja shift staff CS berhasil diperbarui!');
    }

    // 3. Menghapus plot jadwal kerja
    public function destroy($id)
    {
        $shift = EmployeeShift::findOrFail($id);
        $shift->delete();

        return redirect()->back()->with('success', 'Jadwal shift berhasil dihapus dari sistem.');
    }
}