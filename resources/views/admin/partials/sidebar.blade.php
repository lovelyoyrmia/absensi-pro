<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="logo">
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i>📊</i> Dashboard
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
            <i>📅</i> Log Kehadiran
        </a>
        <a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.index') ? 'active' : '' }}">
            <i>📅</i> Pengajuan Izin & Cuti
        </a>
        <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
            <i>👥</i> Karyawan
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <p>{{ auth()->user()->name }}</p>
            <span>Administrator</span>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Keluar</button>
        </form>
    </div>
</aside>