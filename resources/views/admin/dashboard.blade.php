@extends('layouts.admin')
@section('content')
<section class="qr-section">
    <div class="card qr-card">
        <h3>Live Attendance QR</h3>
        <p>Refreshes every 30s</p>
        <div class="qr-flex">
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($inUrl) }}" 
                    alt="Clock In QR" 
                    style="border: 10px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <span class="label in">CLOCK IN</span>
            </div>
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($outUrl) }}" 
                    alt="Clock Out QR" 
                    style="border: 10px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <span class="label out">CLOCK OUT</span>
            </div>
        </div>
    </div>
</section>

<section class="table-section">
    <div class="card">
        <h3>Today's Attendance ({{ date('d M Y') }})</h3>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todayAttendances as $record)
                <tr>
                    <td><strong>{{ $record->user->name }}</strong><br><small>{{ $record->user->nip }}</small></td>
                    <td>{{ $record->clock_in->format('H:i') }}</td>
                    <td>{{ $record->clock_out ? $record->clock_out->format('H:i') : '--:--' }}</td>
                    <td>
                        <span class="status-pill {{ $record->is_late ? 'pill-red' : 'pill-green' }}">
                            {{ $record->is_late ? 'LATE' : 'ON TIME' }}
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
        <h3>All Employees</h3>
        <table>
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
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