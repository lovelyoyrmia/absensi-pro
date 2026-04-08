<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

    <nav class="navbar">
        <span class="brand">Attendance System</span>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>

    <div class="container">
        <div class="profile-card">
            <h1>Hello, {{ auth()->user()->name }}</h1>
            <p class="nip">NIP: {{ auth()->user()->nip }}</p>
        </div>

        <div class="status-section">
            @php
                // Find today's record for the logged-in user
                $today = auth()->user()->attendances()->whereDate('date', now()->toDateString())->first();
            @endphp

            @if(!$today)
                {{-- CASE 1: NOT CLOCKED IN --}}
                <div class="status-card waiting">
                    <h3>Ready to Work?</h3>
                    <p>Scan the code below to <strong>Clock In</strong>.</p>
                    
                    <div class="qr-box" style="margin-top: 20px;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($inUrl) }}" 
                            alt="Clock In QR" 
                            style="border: 10px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <span class="label in" style="display:block; margin-top:10px; color:green; font-weight:bold;">CLOCK IN</span>
                    </div>
                </div>

            @elseif($today && !$today->clock_out)
                {{-- CASE 2: CLOCKED IN (WAITING TO CLOCK OUT) --}}
                <div class="status-card {{ $today->is_late ? 'late' : 'ontime' }}">
                    <h3>Currently Working</h3>
                    <p>You started at: <strong>{{ $today->clock_in->format('H:i') }}</strong></p>
                    <span class="badge">{{ $today->is_late ? 'LATE' : 'ON TIME' }}</span>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid rgba(0,0,0,0.1);">
                    
                    <p>Finished for the day? Scan to <strong>Clock Out</strong>:</p>
                    <div class="qr-box" style="margin-top: 20px;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($outUrl) }}" 
                            alt="Clock Out QR" 
                            style="border: 10px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <span class="label out" style="display:block; margin-top:10px; color:red; font-weight:bold;">CLOCK OUT</span>
                    </div>
                </div>

            @else
                <div class="status-card completed">
                    <h3>Shift Completed</h3>
                    <p>Clock In: {{ $today->clock_in->format('H:i') }}</p>
                    <p>Clock Out: {{ $today->clock_out->format('H:i') }}</p>
                    <div style="font-size: 40px; margin-top: 10px;">✅</div>
                </div>
            @endif
        </div>

        <div class="history-section">
            <h2>Recent Attendance</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->attendances()->latest()->take(5)->get() as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td>{{ $attendance->clock_in->format('H:i') }}</td>
                            <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                            <td>
                                <span class="dot {{ $attendance->is_late ? 'dot-red' : 'dot-green' }}"></span>
                                {{ $attendance->is_late ? 'Late' : 'On Time' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>