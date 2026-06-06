@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1>Kelola Akun Admin</h1>
    <p style="color: #64748b;">Daftarkan atau hapus hak akses supervisor (Admin) aplikasi.</p>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #15803d; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        {{ session('success') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px;">
    
    <div class="card" style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; height: fit-content;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;">Tambah Admin Baru</h3>
        
        <form action="{{ route('owner.admins.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Nama Lengkap</label>
                <input type="text" name="name" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">NIP / ID Kerja</label>
                <input type="text" name="nip" placeholder="ADM-XXXX" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Alamat Email</label>
                <input type="email" name="email" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Password</label>
                <input type="password" name="password" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;" required>
            </div>
            <button type="submit" style="width:100%; background:#4f46e5; color:white; border:none; padding:12px; border-radius:6px; font-weight:600; cursor:pointer;">
                💾 Daftarkan Admin
            </button>
        </form>
    </div>

    <div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 16px;">Nama</th>
                    <th style="padding: 16px;">NIP</th>
                    <th style="padding: 16px;">Email</th>
                    <th style="padding: 16px; text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 16px; font-weight: 600; color: #1e293b;">{{ $admin->name }}</td>
                    <td style="padding: 16px; color: #475569;">{{ $admin->nip }}</td>
                    <td style="padding: 16px; color: #475569;">{{ $admin->email }}</td>
                    <td style="padding: 16px; text-align: center;">
                        <form action="{{ route('owner.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Cabut hak akses admin untuk akun ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                🗑️ Hapus Akses
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">
                        Belum ada supervisor admin tambahan yang didaftarkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection