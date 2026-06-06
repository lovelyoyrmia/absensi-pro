@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1>Global System Settings</h1>
    <p style="color: #64748b;">Modifikasi ambang batas waktu keterlambatan dan tenggat pengajuan operasi harian.</p>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #15803d; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        {{ session('success') }}
    </div>
@endif

<div class="card" style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 600px; margin-top: 20px;">
    <form action="{{ route('owner.settings.update') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 25px;">
            <h3 style="margin-top: 0; font-size: 15px; color: #1e293b; margin-bottom: 5px;">1. Batas Jam Masuk Kerja (Clock In)</h3>
            <p style="font-size: 12px; color: #64748b; margin-top: 0; margin-bottom: 12px;">Karyawan yang melakukan scan QR melewati jam ini otomatis ditandai status <strong>TELAT</strong>.</p>
            <input type="time" name="work_start_time" value="{{ $settings['work_start_time'] ?? '08:00' }}" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; width: 150px;" required>
        </div>

        <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 25px 0;">

        <div style="margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 15px; color: #1e293b; margin-bottom: 5px;">2. Batas Akhir Sistem Absensi (Limit Time)</h3>
            <p style="font-size: 12px; color: #64748b; margin-top: 0; margin-bottom: 12px;">Batas maksimal harian. Melewati jam ini, tombol Clock In terkunci dan dialihkan sebagai <strong>Mencapai Batas</strong>.</p>
            <input type="time" name="work_limit_time" value="{{ $settings['work_limit_time'] ?? '17:00' }}" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; width: 150px;" required>
        </div>

        <button type="submit" style="background: #4f46e5; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
            🔄 Perbarui Aturan Sistem
        </button>
    </form>
</div>
@endsection