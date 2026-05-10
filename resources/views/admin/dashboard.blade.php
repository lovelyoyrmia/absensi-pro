@extends('layouts.admin')
@section('content')
<section class="qr-section">
    <div class="card qr-card" style="text-align: center;">
        <h3>Sistem Absensi QR</h3>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">
            Scan untuk Clock In atau Clock Out
        </p>
        
        <div class="qr-display">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($attendanceUrl) }}" 
                 alt="Universal Attendance QR" 
                 style="border: 15px solid white; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 10px;">
        </div>
        
        <div style="margin-top: 15px;">
            <span class="status-pill pill-green">AKTIF</span>
        </div>
    </div>
</section>

<section class="table-section">
    <div class="card">
        <h3>Kehadiran Hari Ini ({{ date('d M Y') }})</h3>
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todayAttendances as $record)
                <tr>
                    <td><strong>{{ $record->user->name }}</strong><br><small>{{ $record->user->nip }}</small></td>
                    <td>{{ $record->clock_in->format('H:i') }}</td>
                    <td>{{ $record->clock_out ? $record->clock_out->format('H:i') : '--:--' }}</td>
                    <td>{{ $record->address }}</td>
                    <td>
                        <span class="status-pill {{ $record->is_late ? 'pill-red' : 'pill-green' }}">
                            {{ $record->is_late ? 'TELAT' : 'TEPAT' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="table-section">
    <div class="card">
        <h3>Semua Karyawan</h3>
        <table>
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tanggal Gabung</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                <tr>
                    <td><code>{{ $emp->nip }}</code></td>
                    <td>{{ $emp->name }}</td>
                    <td>{{ $emp->email }}</td>
                    <td>{{ $emp->created_at->format('d/m/y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection