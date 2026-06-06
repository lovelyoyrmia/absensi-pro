@extends('layouts.admin') {{-- Menggunakan layout yang sama dengan Admin --}}

@section('content')
<div class="page-header">
    <h1>Executive Overview</h1>
    <p style="color: #64748b;">Statistik operasional perusahaan bulan {{ now()->format('F Y') }}</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <div class="card" style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #4f46e5;">
        <p style="font-size: 14px; color: #64748b; margin: 0;">Total Karyawan</p>
        <h2 style="font-size: 28px; margin: 10px 0;">{{ $totalEmployees }}</h2>
        <small style="color: #4f46e5; font-weight: 600;">Aktif dalam sistem</small>
    </div>

    <div class="card" style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #10b981;">
        <p style="font-size: 14px; color: #64748b; margin: 0;">Total Kehadiran (Bulan Ini)</p>
        <h2 style="font-size: 28px; margin: 10px 0;">{{ $monthlyPresent }}</h2>
        <small style="color: #10b981; font-weight: 600;">Record masuk terverifikasi</small>
    </div>

    <div class="card" style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #ef4444;">
        <p style="font-size: 14px; color: #64748b; margin: 0;">Total Terlambat</p>
        <h2 style="font-size: 28px; margin: 10px 0;">{{ $monthlyLate }}</h2>
        <small style="color: #ef4444; font-weight: 600;">Membutuhkan perhatian</small>
    </div>

    <div class="card" style="background: white; padding: 20px; border-radius: 12px; border-left: 5px solid #f59e0b;">
        <p style="font-size: 14px; color: #64748b; margin: 0;">Jumlah Admin</p>
        <h2 style="font-size: 28px; margin: 10px 0;">{{ $totalAdmins }}</h2>
        <small style="color: #f59e0b; font-weight: 600;">Supervisor sistem</small>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    
    <!-- GRAFIK 7 HARI TERAKHIR -->
    <div class="card" style="background: white; padding: 25px; border-radius: 12px;">
        <h3 style="margin-top: 0; font-size: 16px; margin-bottom: 20px;">Tren Kehadiran (7 Hari Terakhir)</h3>
        <div style="display: flex; align-items: flex-end; gap: 15px; height: 200px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
            @foreach($chartData as $data)
                @php
                    $height = $totalEmployees > 0 ? ($data['count'] / $totalEmployees) * 100 : 0;
                @endphp
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 100%; background: #4f46e5; height: {{ $height }}%; border-radius: 4px 4px 0 0; min-height: 2px;"></div>
                    <span style="font-size: 11px; color: #94a3b8; transform: rotate(-45deg);">{{ $data['date'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- MANAJEMEN ADMIN QUICK VIEW -->
    <div class="card" style="background: white; padding: 25px; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 16px;">Daftar Admin</h3>
            <a href="#" style="font-size: 12px; color: #4f46e5; text-decoration: none; font-weight: 600;">+ Tambah</a>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            @foreach($admins as $admin)
            <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <div style="width: 35px; height: 35px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #4f46e5; font-weight: bold; font-size: 12px;">
                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                </div>
                <div style="flex: 1;">
                    <p style="margin: 0; font-size: 14px; font-weight: 600; color: #1e293b;">{{ $admin->name }}</p>
                    <p style="margin: 0; font-size: 12px; color: #94a3b8;">{{ $admin->email }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection