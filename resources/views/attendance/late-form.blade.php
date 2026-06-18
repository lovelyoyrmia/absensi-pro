<form action="{{ route('attendance.store-late') }}" method="POST" enctype="multipart/form-data" class="card">
    @csrf
    <h3>Form Keterangan Keterlambatan</h3>
    <p>Anda terdeteksi terlambat masuk shift. Silakan isi dokumen di bawah ini:</p>
    
    <div class="form-group">
        <label>Alasan Keterlambatan</label>
        <textarea name="late_reason" class="form-control" required></textarea>
    </div>
    
    <div class="form-group">
        <label>Unggah Foto Bukti (Kondisi Jalan/Surat)</label>
        <input type="file" name="late_proof" class="form-control" accept="image/*" required>
    </div>
    
    <button type="submit" class="btn-submit">Simpan & Masuk Kerja</button>
</form>