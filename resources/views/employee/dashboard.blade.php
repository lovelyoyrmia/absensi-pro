@php
    use Carbon\Carbon;

    $now = Carbon::now()->setTimezone('Asia/Jakarta');
    $todayDate = $now->toDateString();
    
    // 1. Cari rekam jejak absensi/cuti hari ini
    $today = auth()->user()->attendances()->whereDate('date', $todayDate)->first();

    // 2. Set standar default untuk divisi Finance (Pagi)
    $shiftName = "Pagi";
    $startTimeString = "08:00"; 

    // 3. Jika karyawan adalah CS, ambil jadwal aslinya dari tabel 'employee_shifts'
    if (auth()->user()->division === 'CS') {
        $schedule = DB::table('employee_shifts')
            ->where('user_id', auth()->id())
            ->whereDate('date', $todayDate)
            ->first();

        if ($schedule) {
            $shiftName = $schedule->shift_name;
            $startTimeString = $schedule->start_time; // Misal: 07:00, 15:00, atau 23:00
        }
    }

    // 4. LOGIKA PINTAR BATAS ABSEN (Mendukung Shift Malam lintas hari)
    // Buat objek waktu mulai shift yang sesungguhnya pada hari ini
    $shiftStartDateTime = Carbon::parse($todayDate . ' ' . $startTimeString, 'Asia/Jakarta');
    
    // Batas toleransi penguncian tombol: 4 jam setelah shift dimulai
    $limitDateTime = $shiftStartDateTime->copy()->addHours(4);

    // Kunci sukses: Bandingkan objek Carbon Waktu Sekarang dengan Waktu Batas Maksimal
    $isTooLate = $now->greaterThan($limitDateTime);
    
    // Format teks untuk ditampilkan ke layar HP karyawan
    $limitTimeDisplay = $limitDateTime->format('H:i');

    // 5. Validasi Tombol Clock Out (Wajib Kerja Minimal 8 Jam)
    $canAccessClockOut = false;
    if ($today && $today->status === 'masuk' && !$today->clock_out) {
        $workDurationMinutes = Carbon::parse($today->clock_in)->diffInMinutes($now);
        if ($workDurationMinutes >= (8 * 60)) {
            $canAccessClockOut = true;
        }
    }
@endphp

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
            <p class="nip">NIP: {{ auth()->user()->nip }} | Divisi: {{ auth()->user()->division ?? 'Finance' }}</p>
        </div>

        <div class="status-section">
        @if(!$today)
            <div class="status-card waiting">
                <h3>Siap Bekerja Hari Ini?</h3>
                
                <div style="background: #e0e7ff; color: #4f46e5; padding: 12px; border-radius: 8px; margin: 15px 0; font-size: 14px; font-weight: 600; text-align: center;">
                    📌 Jadwal Anda: {{ $shiftName }} (Mulai: {{ Carbon::parse($startTimeString)->format('H:i') }} WIB)
                </div>
                
                @if($isTooLate)
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <p><strong>Maaf, Batas Absen Berakhir</strong></p>
                        <p style="font-size: 13px;">Batas waktu toleransi Clock In untuk {{ $shiftName }} Anda adalah pukul {{ $limitTimeDisplay }} WIB. Silakan hubungi Admin.</p>
                    </div>
                @else
                    <p>Gunakan kamera untuk melakukan <strong>Clock In</strong> atau ajukan keterangan jika berhalangan.</p>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 15px;">(Batas maksimal masuk jam {{ $limitTimeDisplay }} WIB)</p>
                    <div id="reader-wrapper" style="display: none; margin-top: 20px;">
                        <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto; border-radius: 10px; overflow: hidden; border: 2px solid #ddd;"></div>
                        <button type="button" onclick="stopScanner()" class="logout-btn" style="margin-top: 10px;">Batal</button>
                    </div>

                    <div id="scan-init-btn" style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <button type="button" onclick="startScanner()" class="login-btn" style="width: 100%; max-width: 300px;">
                            📸 Buka Kamera Scan
                        </button>
                        
                        <button type="button" onclick="toggleFormIzin()" class="btn-toggle-izin" id="btn-toggle-text" style="width: 100%; max-width: 300px;">
                            📝 Ajukan Sakit / Izin / Cuti
                        </button>
                    </div>

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
                <p style="margin-top: 10px; color: #854d0e;">Anda tidak perlu mengabsen karena sistem mencatat keterangan: "<i>{{ $today->notes }}</i>"</p>
                <div style="font-size: 40px; margin-top: 10px;">📅</div>
            </div>

        @elseif($today && !$today->clock_out)
            <div class="status-card {{ $today->is_late ? 'late' : 'ontime' }}">
                <h3>Sedang Bekerja ({{ $today->shift_name ?? 'Pagi' }})</h3>
                <p>Masuk pada: <strong>{{ Carbon::parse($today->clock_in)->format('H:i') }}</strong></p>
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
                    <div style="background: #fff7ed; color: #c2410c; padding: 12px; border-radius: 8px; border: 1px solid #ffedd5; font-size: 13px; font-weight: 600; text-align: center;">
                        ⚠️ Tombol Pulang Terkunci: Anda wajib menyelesaikan durasi kerja minimal 8 jam kerja sebelum diizinkan Clock Out.
                    </div>
                @endif
            </div>
        @else
            <div class="status-card completed">
                <h3>Shift Selesai</h3>
                <div style="font-size: 40px; margin-top: 10px;">✅</div>
                <p style="font-size: 13px; color: #64748b;">Sampai jumpa di shift berikutnya!</p>
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
                                document.cookie = "user_lat=" + lat + "; max-age=60; path=/; SameSite=Lax";
                                document.cookie = "user_lng=" + lng + "; max-age=60; path=/; SameSite=Lax";
                                
                                setTimeout(() => {
                                    window.location.href = decodedText;
                                }, 300);
                            });
                        }
                    ).catch(err => alert("Kamera Error: " + err));
                },
                (error) => {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            alert("Akses lokasi diblokir browser. Silakan izinkan lokasi di pengaturan browser Anda.");
                            break;
                        case error.POSITION_UNAVAILABLE:
                            alert("Sinyal GPS lemah atau informasi lokasi tidak tersedia.");
                            break;
                        case error.TIMEOUT:
                            alert("Waktu pencarian lokasi habis (Sinyal GPS tidak stabil).");
                            break;
                    }
                },
                { 
                    enableHighAccuracy: false, 
                    timeout: 10000,         
                    maximumAge: 60000        
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