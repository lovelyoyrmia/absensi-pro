<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keterangan Keterlambatan - Absence Pro</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .late-container {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            box-sizing: border-box;
        }
        .late-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .warning-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        h2 {
            color: #1e293b;
            margin: 0 0 10px 0;
            font-size: 22px;
            font-weight: 700;
        }
        p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 25px 0;
        }
        .info-box {
            background: #fff7ed;
            border: 1px solid #ffedd5;
            padding: 12px;
            border-radius: 8px;
            color: #c2410c;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
        }
        textarea.form-control {
            resize: none;
        }
        .btn-block {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }
        .btn-submit {
            background: #4f46e5;
            color: white;
            margin-bottom: 10px;
        }
        .btn-submit:hover {
            background: #4338ca;
        }
        .btn-cancel {
            background: transparent;
            color: #64748b;
            text-decoration: none;
            display: block;
            font-size: 14px;
            margin-top: 15px;
        }
        #image-preview {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>
<body>

<div class="late-container">
    <div class="late-card">
        <div class="warning-icon">⚠️</div>
        <h2>Konfirmasi Keterlambatan</h2>
        <p>Anda terdeteksi melakukan absensi melewati jam masuk shift. Silakan lengkapi dokumen di bawah ini untuk disimpan ke berkas laporan staff.</p>

        @if(session('pending_attendance'))
            <div class="info-box">
                ⏱️ Terdeteksi Masuk Pada: {{ \Carbon\Carbon::parse(session('pending_attendance')['clock_in'])->format('H:i:s') }} WIB<br>
                📍 Lokasi Terdeteksi: {{ strlen(session('pending_attendance')['address']) > 60 ? substr(session('pending_attendance')['address'], 0, 60) . '...' : session('pending_attendance')['address'] }}
            </div>
        @endif

        <form action="{{ route('attendance.store-late') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="late_reason">Alasan Keterlambatan</label>
                <textarea name="late_reason" id="late_reason" rows="3" class="form-control" placeholder="Contoh: Ban motor bocor di jalan tol / Terjebak kemacetan di persimpangan..." required></textarea>
            </div>

            <div class="form-group">
                <label for="late_proof">Lampirkan Bukti Foto</label>
                <input type="file" name="late_proof" id="late_proof" class="form-control" accept="image/*" capture="environment" onchange="previewFoto(event)" required>
                <img id="image-preview" alt="Pratinjau Bukti">
            </div>

            <button type="submit" class="btn-block btn-submit">💾 Simpan Absen Masuk</button>
            
            <a href="{{ route('dashboard') }}" class="btn-cancel">Batal & Kembali</a>
        </form>
    </div>
</div>

<script>
    // Fungsi JavaScript instan untuk memunculkan preview foto setelah dijepret kamera HP
    function previewFoto(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('image-preview');
            output.src = reader.result;
            output.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
</body>
</html>