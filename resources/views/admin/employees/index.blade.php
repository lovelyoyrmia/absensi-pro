@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1>Manajemen Karyawan</h1>
    <a href="{{ route('admin.employees.create') }}" class="btn-add">+ Tambah Karyawan Baru</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Jabatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td><code>{{ $employee->nip }}</code></td>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->email }}</td>
                <td>{{ $employee->department }}</td>
                <td>
                    <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn-edit">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection