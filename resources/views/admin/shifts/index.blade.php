@extends('layouts.admin')

@section('content')
<div class="page-header" style="margin-bottom: 25px;">
    <h1>📅 Penjadwalan Shift Staff CS</h1>
    <p style="color: #64748b;">Atur plot kerja acak mingguan khusus untuk tim Customer Service.</p>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #15803d; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; border: 1px solid #bbf7d0;">
        {{ session('success') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    
    <div class="card" style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; height: fit-content;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; color: #1e293b;">Plot Shift Baru</h3>
        
        <form action="{{ route('admin.shifts.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Pilih Staff CS</label>
                <select name="user_id" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;" required>
                    <option value="">-- Pilih Anggota CS --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} (NIP: {{ $emp->nip }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Pilih Tanggal Kerja</label>
                <input type="date" name="date" value="{{ $selectedDate }}" style="width:100%; padding:9px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Pilih Kategori Shift</label>
                <select name="shift_name" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;" required>
                    <option value="Shift 1">🌅 Shift 1 (07:00 - 15:00)</option>
                    <option value="Shift 2">🌆 Shift 2 (15:00 - 23:00)</option>
                    <option value="Shift 3">🌃 Shift 3 (23:00 - 07:00)</option>
                </select>
            </div>

            <button type="submit" style="width:100%; background:#4f46e5; color:white; border:none; padding:12px; border-radius:6px; font-weight:600; cursor:pointer;">
                💾 Terapkan Jadwal Shift
            </button>
        </form>
    </div>

    <div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin:0; font-size: 16px;">Jadwal Aktif Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</h3>
            
            <form action="{{ route('admin.shifts.index') }}" method="GET">
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
            </form>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px;">Nama Karyawan</th>
                    <th style="padding: 12px;">Nama Shift</th>
                    <th style="padding: 12px; text-align: center;">Jam Kerja</th>
                    <th style="padding: 12px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px;">
                        <strong>{{ $shift->user->name }}</strong><br>
                        <small style="color:#94a3b8;">NIP: {{ $shift->user->nip }}</small>
                    </td>
                    <td style="padding: 12px;">
                        @if($shift->shift_name === 'Shift 1')
                            <span style="color:#22c55e; font-weight:600;">🌅 {{ $shift->shift_name }}</span>
                        @elseif($shift->shift_name === 'Shift 2')
                            <span style="color:#3b82f6; font-weight:600;">🌆 {{ $shift->shift_name }}</span>
                        @else
                            <span style="color:#6366f1; font-weight:600;">🌃 {{ $shift->shift_name }}</span>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: center; color:#475569; font-family: monospace;">
                        {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal kerja shift orang ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer; font-size: 13px;">
                                ✕ Batalkan
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">
                        Belum ada plot jadwal kerja CS untuk tanggal ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection