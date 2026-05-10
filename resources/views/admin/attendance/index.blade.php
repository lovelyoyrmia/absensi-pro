@extends('layouts.admin')

@section('content')
<div class="page-header">
    <div>
        <h1>Log Kehadiran</h1>
        <p style="color: #64748b;">Lihat record untuk: <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong></p>
    </div>
    
    <form action="{{ route('admin.attendance.index') }}" method="GET" class="filter-form">
        <input type="date" name="date" value="{{ $selectedDate }}" class="date-input">
        <button type="submit" class="btn-filter">Filter</button>
        <a href="{{ route('admin.attendance.index') }}" class="btn-reset">Hari ini</a>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $record)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $record->user->name }}</strong>
                        <small style="color: #94a3b8;">{{ $record->user->nip }}</small>
                    </div>
                </td>
                <td>{{ $record->clock_in->format('H:i') }}</td>
                <td>{{ $record->clock_out ? $record->clock_out->format('H:i') : '--:--' }}</td>
                <td>
                    <span class="status-pill {{ $record->is_late ? 'pill-red' : 'pill-green' }}">
                        {{ $record->is_late ? 'TELAT' : 'TEPAT' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                    Belum ada yang clock in hari ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection