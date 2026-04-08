@extends('layouts.admin')

@section('content')
<div class="page-header">
    <h1>Add New Employee</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn-edit">Back to List</a>
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
            <label>Full Name</label>
            <input type="text" name="name" class="form-input" placeholder="e.g. John Doe" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-input" placeholder="john@company.com" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-input" placeholder="Minimum 8 characters" required>
            <small style="color: #64748b;">The NIP will be generated automatically after saving.</small>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-add" style="width: 100%; border: none; cursor: pointer;">
                Save Employee & Generate NIP
            </button>
        </div>
    </form>
</div>
@endsection