@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1>Edit Employee: {{ $employee->name }}</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn-edit">Cancel</a>
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

    <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label>NIP (Read Only)</label>
            <input type="text" class="form-input" value="{{ $employee->nip }}" disabled style="background: #f1f5f9; cursor: not-allowed;">
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-input" value="{{ old('name', $employee->name) }}" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-input" value="{{ old('email', $employee->email) }}" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
            <small style="color: #64748b;">Only fill this if you want to reset their login password.</small>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-add" style="width: 100%; border: none; cursor: pointer;">
                Update Employee Details
            </button>
        </div>
    </form>
</div>
@endsection