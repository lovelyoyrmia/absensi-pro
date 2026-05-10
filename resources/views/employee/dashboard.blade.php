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
        <span class="brand">Sistem Absensi</span>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Keluar</button>
        </form>
    </nav>

    <div class="container">
        <div class="profile-card">
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p class="nip">NIP: {{ auth()->user()->nip }}</p>
        </div>

        <div class="status-section">
        @php
            $today = auth()->user()->attendances()->whereDate('date', now()->toDateString())->first();
            $currentTime = now()->format('H:i');
            $isTooLate = $currentTime > '20:00';
        @endphp

        @if(!$today)
            <div class="status-card waiting">
                <h3>Siap Bekerja?</h3>
                
                @if($isTooLate)
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <p><strong>Maaf, Batas Absen Berakhir</strong></p>
                        <p style="font-size: 13px;">Batas waktu Clock In adalah pukul 20:00. Silakan hubungi Admin.</p>
                    </div>
                @else
                    <p>Gunakan kamera untuk melakukan <strong>Clock In</strong>.</p>
                    <p style="font-size: 12px; color: #64748b;">(Batas maksimal jam 20:00)</p>

                    <div id="reader-wrapper" style="display: none; margin-top: 20px;">
                        <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto; border-radius: 10px; overflow: hidden; border: 2px solid #ddd;"></div>
                        <button type="button" onclick="stopScanner()" class="logout-btn" style="margin-top: 10px;">Batal</button>
                    </div>

                    <div id="scan-init-btn">
                        <button type="button" onclick="startScanner()" class="login-btn" style="margin-top: 20px;">
                            📸 Buka Kamera Scan
                        </button>
                    </div>
                @endif
            </div>

        @elseif($today && !$today->clock_out)
            <div class="status-card {{ $today->is_late ? 'late' : 'ontime' }}">
                <h3>Sedang Bekerja</h3>
                <p>Masuk pada: <strong>{{ $today->clock_in->format('H:i') }}</strong></p>
                <span class="badge">{{ $today->is_late ? 'TELAT' : 'TEPAT' }}</span>
                
                <hr style="margin: 20px 0; border-top: 1px solid rgba(0,0,0,0.1);">
                
                <div id="reader-wrapper" style="display: none; margin-top: 20px;">
                    <div id="reader"></div>
                    <button type="button" onclick="stopScanner()" class="logout-btn">Batal</button>
                </div>
                
                <div id="scan-init-btn">
                    <button type="button" onclick="startScanner()" class="login-btn">
                        📸 Scan Clock Out
                    </button>
                </div>
            </div>
        @else
            <div class="status-card completed">
                <h3>Shift Selesai</h3>
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
                            <th>Tanggal</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->attendances()->latest()->take(5)->get() as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td>{{ $attendance->clock_in->format('H:i') }}</td>
                            <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                            <td>{{ $attendance->address }}</td>
                            <td>
                                <span class="dot {{ $attendance->is_late ? 'dot-red' : 'dot-green' }}"></span>
                                {{ $attendance->is_late ? 'Telat' : 'Tepat' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    let html5QrCode;

    function startScanner() {
        if (navigator.geolocation) {
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('reader-wrapper').style.display = 'block';
                    document.getElementById('scan-init-btn').style.display = 'none';

                    html5QrCode = new Html5Qrcode("reader");
                    html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: 250 },
                        (decodedText) => {
                            html5QrCode.stop().then(() => {
                                document.cookie = "user_lat=" + lat + "; max-age=60; path=/";
                                document.cookie = "user_lng=" + lng + "; max-age=60; path=/";
                                window.location.href = decodedText;
                            });
                        }
                    ).catch(err => alert("Kamera Error: " + err));
                },
                (error) => {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            alert("Anda harus mengizinkan akses lokasi untuk melakukan absensi.");
                            break;
                        case error.POSITION_UNAVAILABLE:
                            alert("Informasi lokasi tidak tersedia.");
                            break;
                        case error.TIMEOUT:
                            alert("Waktu permintaan lokasi habis.");
                            break;
                    }
                },
                {
                    enableHighAccuracy: true, 
                    timeout: 5000,           
                    maximumAge: 0
                }
            );
        } else {
            alert("Browser Anda tidak mendukung deteksi lokasi.");
        }
    }
    function stopScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                document.getElementById('reader-wrapper').style.display = 'none';
                document.getElementById('scan-init-btn').style.display = 'block';
            });
        }
    }
</script>
</html>