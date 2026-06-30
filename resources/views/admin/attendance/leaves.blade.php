@extends('layouts.admin')

@section('content')
<div class="page-header">
    <div>
        <h1>Persetujuan Cuti & Izin Karyawan</h1>
        <p style="color: #64748b;">Daftar pengajuan izin tidak masuk yang membutuhkan tindakan Anda.</p>
    </div>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #15803d; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; border: 1px solid #bbf7d0;">
        {{ session('success') }}
    </div>
@endif

@if(session('info'))
    <div style="background: #fef3c7; color: #d97706; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; border: 1px solid #fde68a;">
        {{ session('info') }}
    </div>
@endif

<div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-top: 20px;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <th style="padding: 16px;">Karyawan</th>
                <th style="padding: 16px;">Tanggal Pengajuan</th>
                <th style="padding: 16px;">Kategori</th>
                <th style="padding: 16px;">Alasan / Keterangan</th>
                <th style="padding: 16px;">Status</th>
                <th style="padding: 16px;">Bukti</th>
                <th style="padding: 16px; text-align: center;">Aksi Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submissions as $data)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px;">
                    <div style="display: flex; flex-direction: column;">
                        <strong style="color: #1e293b;">{{ $data->user->name }}</strong>
                        <small style="color: #94a3b8;">{{ $data->user->nip }}</small>
                    </div>
                </td>
                <td style="padding: 16px; color: #334155;">
                    {{ $data->date->format('d M Y') }}
                </td>
                <td style="padding: 16px;">
                    @if($data->status === 'sakit')
                        <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-size: 12p6; font-weight: 600;">😷 Sakit</span>
                    @elseif($data->status === 'izin')
                        <span style="background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">✉️ Izin</span>
                    @else
                        <span style="background: #fef9c3; color: #854d0e; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">🌴 Cuti</span>
                    @endif
                </td>
                <td style="padding: 16px; color: #334155; font-style: italic; max-width: 250px; word-wrap: break-word;">
                    "{{ $data->notes }}"
                </td>
                <td style="padding: 16px; text-align: center;">
                    @if($data->approval_status === 'pending')
                        <span style="background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 50px; font-size: 12px; font-weight: bold;">PENDING</span>
                    @elseif($data->approval_status === 'approved')
                        <span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 50px; font-size: 12px; font-weight: bold;">APPROVED</span>
                    @else
                        <span style="background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 50px; font-size: 12px; font-weight: bold;">REJECTED</span>
                        @if($data->reject_reason)
                            <br><small style="color: #ef4444; font-size: 11px;">Ket: {{ $data->reject_reason }}</small>
                        @endif
                    @endif
                </td>
                <td style="padding: 12px; text-align: center;">
                    @if($data->late_proof)
                        @php
                            // Pecah string "leave_proofs/namafile.jpg" menjadi array
                            $parts = explode('/', $data->late_proof);
                            $folder = $parts[0] ?? 'leave_proofs';
                            $filename = $parts[1] ?? '';
                        @endphp
                        <img src="{{ route('storage.bypass', ['folder' => $folder, 'filename' => $filename]) }}" 
                            alt="Evidence" 
                            onclick="bukaModal(this.src)"
                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 1px solid #cbd5e1; transition: transform 0.2s;"
                            onmouseover="this.style.transform='scale(1.1)'" 
                            onmouseout="this.style.transform='scale(1)'">
                        <br>
                        <small style="color: #4f46e5; font-size: 11px; cursor: pointer; font-weight: 600;" onclick="bukaModal('{{ route('storage.bypass', ['folder' => $folder, 'filename' => $filename]) }}')">Zoom Foto</small>
                    @else
                        <span style="color: #94a3b8; font-size: 13px; font-style: italic;">Tidak ada bukti</span>
                    @endif
                </td>
                <td style="padding: 16px; text-align: center;">
                    @if($data->approval_status === 'pending')
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <form action="{{ route('admin.leaves.approve', $data->id) }}" method="POST" onsubmit="return confirm('Setujui pengajuan izin ini?')">
                                @csrf
                                <button type="submit" style="background: #22c55e; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px;">
                                    ✓ Setujui
                                </button>
                            </form>

                            <button type="button" onclick="pemicuTolak({{ $data->id }})" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px;">
                                ✕ Tolak
                            </button>
                        </div>

                        <form id="form-tolak-{{ $data->id }}" action="{{ route('admin.leaves.reject', $data->id) }}" method="POST" style="display:none;">
                            @csrf
                            <input type="hidden" name="reject_reason" id="input-alasan-{{ $data->id }}">
                        </form>
                    @else
                        <span style="color: #94a3b8; font-size: 13px; font-style: italic;">Selesai Diperiksa</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 50px; color: #94a3b8; font-style: italic;">
                    Tidak ada pengajuan izin atau cuti baru yang perlu diproses. Bersih! ✨
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div id="modalEvidence" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center;">
    <div style="position: relative; max-width: 80%; max-height: 80%;">
        <span onclick="tutupModal()" style="position: absolute; top: -40px; right: 0; color: white; font-size: 30px; font-weight: bold; cursor: pointer;">&times;</span>
        <img id="imgJumbo" src="" style="max-width: 100%; max-height: 80vh; border-radius: 8px; object-fit: contain; border: 3px solid white;">
    </div>
</div>
<script>
    // FUNGSI BARU: KONTROL POP-UP EVIDENCE
    function bukaModal(src) {
        document.getElementById('imgJumbo').src = src;
        document.getElementById('modalEvidence').style.display = 'flex';
    }

    function tutupModal() {
        document.getElementById('modalEvidence').style.display = 'none';
    }

    // Tutup modal otomatis jika Admin klik area hitam di luar gambar
    window.onclick = function(event) {
        const modal = document.getElementById('modalEvidence');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    function pemicuTolak(id) {
        let alasan = prompt("Masukkan alasan mengapa pengajuan izin ini ditolak:");
        if (alasan === null) return; 
        if (alasan.trim() === "") {
            alert("Alasan penolakan wajib diisi agar karyawan tahu kendalanya!");
            return;
        }
        
        document.getElementById('input-alasan-' + id).value = alasan;
        document.getElementById('form-tolak-' + id).submit();
    }
</script>
@endsection