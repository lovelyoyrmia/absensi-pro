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
                        <span style="background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">😷 SAKIT</span>
                    @elseif($data->status === 'izin')
                        <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">✉️ IZIN</span>
                    @else
                        <span style="background: #fef08a; color: #a16207; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">🌴 CUTI</span>
                    @endif
                </td>
                <td style="padding: 16px; color: #475569; max-width: 300px; word-wrap: break-word;">
                    "{{ $data->notes }}"
                </td>
                <td style="padding: 16px; text-align: center;">
                    <div style="display: flex; gap: 8px; justify-content: center;">
                        <!-- TOMBOL SETUJU -->
                        <form action="{{ route('admin.leaves.approve', $data->id) }}" method="POST" onsubmit="return confirm('Setujui pengajuan izin ini?')">
                            @csrf
                            <button type="submit" style="background: #10b981; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">
                                ✓ Setujui
                            </button>
                        </form>

                        <!-- TOMBOL TOLAK -->
                        <form action="{{ route('admin.leaves.reject', $data->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan izin ini?')">
                            @csrf
                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">
                                ✕ Tolak
                            </button>
                        </form>
                    </div>
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
@endsection