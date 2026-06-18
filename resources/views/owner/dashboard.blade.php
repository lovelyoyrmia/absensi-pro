@extends('layouts.admin') {{-- Menggunakan layout yang sama dengan Admin --}}

@section('content')
<div class="page-header">
    <h1>Executive Overview</h1>
    <p style="color: #64748b;">Statistik operasional perusahaan bulan {{ now()->format('F Y') }}</p>
</div>

<div style="background: #1e293b; color: white; padding: 25px; border-radius: 12px; margin-top: 30px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
    <div>
        <p style="color: #94a3b8; margin: 0; font-size: 13px;">Struktur Organisasi</p>
        <h3 style="margin: 5px 0 0 0; font-size: 22px;">Total Karyawan: {{ $totalEmployees }} Staff</h3>
    </div>
    <div>
        <p style="color: #94a3b8; margin: 0; font-size: 13px;">Akumulasi Kedisiplinan</p>
        <h3 style="margin: 5px 0 0 0; font-size: 16px; color: #f59e0b;">🔴 Total Kasus Telat: {{ $globalLateCount }} Kali</h3>
    </div>
    <div>
        <p style="color: #cbd5e1; margin: 0; font-size: 13px; font-weight: bold; color: #10b981;">💰 Total Hemat Saldo Potongan</p>
        <h3 style="margin: 5px 0 0 0; font-size: 24px; color: #10b981;">Rp {{ number_format($globalDeductionTotal, 0, ',', '.') }}</h3>
    </div>
</div>

<div style="margin-top: 15px; display: flex; gap: 10px; background: #334155; padding: 15px; border-radius: 8px; color: white; font-size: 13px;">
    <div><strong>Kehadiran Terdistribusi:</strong></div>
    <div style="border-right: 1px solid #475569; padding-right: 10px;">📅 Harian: <strong>{{ $presentToday }}</strong></div>
    <div style="border-right: 1px solid #475569; padding-right: 10px;">📅 Mingguan: <strong>{{ $presentThisWeek }}</strong></div>
    <div>📅 Bulanan: <strong>{{ $presentThisMonth }}</strong></div>
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