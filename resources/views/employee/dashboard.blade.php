<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="icon" type="image/jpg" href="{{ asset('images/logo.jpeg') }}">
    <style>
        /* CSS Tambahan untuk Form Pengajuan Non-Kehadiran */
        .form-izin {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-align: left;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }
        .btn-submit-izin {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
        }
        .btn-toggle-izin {
            background: transparent;
            color: #4f46e5;
            border: 1px solid #4f46e5;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            max-width: 300px;
            font-size: 14px;
        }
        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
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
        
        @if(session('success'))
            <div class="alert-box" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-box" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                {{ session('error') }}
            </div>
        @endif

        <div class="profile-card">
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p class="nip">NIP: {{ auth()->user()->nip }}</p>
        </div>

        <div class="status-section">
        @php
            $today = auth()->user()->attendances()->whereDate('date', now()->setTimezone('Asia/Jakarta')->toDateString())->first();
            $currentTime = now()->setTimezone('Asia/Jakarta')->format('H:i');
            $isTooLate = $currentTime > '17:00';
            $canAccessClockOut = $today && $today->status === 'masuk' && !$today->clock_out && $currentTime >= '17:00';
        @endphp

        @if(!$today)
            <div class="status-card waiting">
                <h3>Siap Bekerja?</h3>
                
                @if($isTooLate)
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <p><strong>Maaf, Batas Absen Berakhir</strong></p>
                        <p style="font-size: 13px;">Batas waktu Clock In adalah pukul 17:00. Silakan hubungi Admin.</p>
                    </div>
                @else
                    <p>Gunakan kamera untuk melakukan <strong>Clock In</strong> atau ajukan keterangan jika berhalangan.</p>
                    <p style="font-size: 12px; color: #64748b;">(Batas maksimal jam 17:00)</p>

                    <div id="reader-wrapper" style="display: none; margin-top: 20px;">
                        <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto; border-radius: 10px; overflow: hidden; border: 2px solid #ddd;"></div>
                        <button type="button" onclick="stopScanner()" class="logout-btn" style="margin-top: 10px;">Batal</button>
                    </div>

                    <div id="scan-init-btn" style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <button type="button" onclick="startScanner()" class="login-btn" style="margin-top: 20px; width: 100%; max-width: 300px;">
                            📸 Buka Kamera Scan
                        </button>
                        
                        <button type="button" onclick="toggleFormIzin()" class="btn-toggle-izin" id="btn-toggle-text">
                            📝 Ajukan Sakit / Izin / Cuti
                        </button>
                    </div>

                    <!-- FORM COMPONENT FOR LEAVE/SICK SUBMISSION -->
                    <div id="form-izin-container" class="form-izin">
                        <h4 style="margin-top: 0; color: #1e293b; font-size: 16px; margin-bottom: 15px;">Form Keterangan Tidak Masuk</h4>
                        <form action="{{ route('attendance.store-izin') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="status">Kategori Halangan</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="sakit">😷 Sakit (Butuh Istirahat)</option>
                                    <option value="izin">✉️ Izin Alasan Sah</option>
                                    <option value="cuti">🌴 Cuti Tahunan / Bersama</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="notes">Detail Alasan</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Tulis alasan singkat ketidakhadiran Anda..." required></textarea>
                            </div>
                            <button type="submit" class="btn-submit-izin">Kirim Pengajuan</button>
                        </form>
                    </div>
                @endif
            </div>

        @elseif($today && $today->status !== 'masuk')
            <div class="status-card completed" style="background: #fef08a; border-left: 5px solid #eab308; color: #713f12;">
                <h3>Status Hari Ini: <span style="text-transform: uppercase; font-weight: bold;">{{ $today->status }}</span></h3>
                <p style="margin-top: 10px; color: #854d0e;">Keterangan Anda: "<i>{{ $today->notes }}</i>"</p>
                <div style="font-size: 40px; margin-top: 10px;">📅</div>
            </div>

        @elseif($today && !$today->clock_out)
            <!-- KONDISI JIKA SEDANG MASUK KERJA DAN BELUM CLOCK OUT -->
            <div class="status-card {{ $today->is_late ? 'late' : 'ontime' }}">
                <h3>Sedang Bekerja</h3>
                <p>Masuk pada: <strong>{{ $today->clock_in->format('H:i') }}</strong></p>
                <span class="badge">{{ $today->is_late ? 'TELAT' : 'TEPAT' }}</span>
                
                <hr style="margin: 20px 0; border-top: 1px solid rgba(0,0,0,0.1);">
                
                <div id="reader-wrapper" style="display: none; margin-top: 20px;">
                    <div id="reader"></div>
                    <button type="button" onclick="stopScanner()" class="logout-btn">Batal</button>
                </div>
                
                @if ($canAccessClockOut)
                    <div id="scan-init-btn">
                        <button type="button" onclick="startScanner()" class="login-btn">
                            📸 Scan Clock Out
                        </button>
                    </div>
                @else
                    <p style="font-size: 13px; color: #64748b; font-style: italic;">Tombol Clock Out otomatis terbuka pukul 17:00.</p>
                @endif
            </div>
        @else
            <div class="status-card completed">
                <h3>Shift Selesai</h3>
                <div style="font-size: 40px; margin-top: 10px;">✅</div>
            </div>
        @endif
    </div>

        <div class="history-section">
            <h2>Daftar Kehadiran</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Keterangan / Lokasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(auth()->user()->attendances()->latest()->take(5)->get() as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td>{{ $attendance->status == 'masuk' ? $attendance->clock_in->format('H:i') : '-' }}</td>
                            <td>{{ ($attendance->status == 'masuk' && $attendance->clock_out) ? $attendance->clock_out->format('H:i') : '-' }}</td>
                            <td>
                                @if($attendance->status == 'masuk')
                                    {{ $attendance->address }}
                                @else
                                    <span style="color: #64748b; font-style: italic;">({{ ucfirst($attendance->status) }}) {{ $attendance->notes }}</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->status == 'masuk')
                                    <span class="dot {{ $attendance->is_late ? 'dot-red' : 'dot-green' }}"></span>
                                    {{ $attendance->is_late ? 'TELAT' : 'TEPAT' }}
                                @else
                                    <span class="dot" style="background-color: #eab308;"></span>
                                    {{ ucfirst($attendance->status) }}
                                @endif
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

    function toggleFormIzin() {
        const form = document.getElementById('form-izin-container');
        const btnText = document.getElementById('btn-toggle-text');
        
        if (form.style.display === 'block') {
            form.style.display = 'none';
            btnText.innerText = '📝 Ajukan Sakit / Izin / Cuti';
        } else {
            form.style.display = 'block';
            btnText.innerText = '❌ Batalkan Pengajuan';
            stopScanner(); 
        }
    }

    function startScanner() {
        document.getElementById('form-izin-container').style.display = 'none';
        document.getElementById('btn-toggle-text').innerText = '📝 Ajukan Sakit / Izin / Cuti';

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
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
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