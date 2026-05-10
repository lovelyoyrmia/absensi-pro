@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1>Tambah Karyawan Baru</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn-edit">Kembali</a>
</div>

<div class="card">
    @if ($errors->any())
        <div class="error-box" style="margin-bottom: 20px; color: red; background: #fee2e2; padding: 10px; border-radius: 8px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.employees.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="name" class="form-input" placeholder="e.g. John Doe" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-input" placeholder="john@company.com" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label>Kata Sandi</label>
            <input type="password" name="password" class="form-input" placeholder="Minimum 8 characters" required>
            <small style="color: #64748b;">NIP akan di generate otomatis setelah disimpan.</small>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-add" style="width: 100%; border: none; cursor: pointer;">
                Simpan Karyawan & Generate NIP
            </button>
        </div>
    </form>
</div>
@endsection