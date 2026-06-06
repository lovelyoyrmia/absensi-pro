<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="logo">
        </div>
    </div>

    <nav class="sidebar-nav">
        @if(auth()->user()->role === 'owner')
            <a href="{{ route('owner.dashboard') }}" class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                🏢 Executive Dashboard
            </a>
        @else
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                📊 Dashboard
            </a>
        @endif

        <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}">
            📊 Log Kehadiran
        </a>
        
        <a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.index') ? 'active' : '' }}">
            📝 Persetujuan Cuti
        </a>

        @if(auth()->user()->role === 'owner')
            <div style="margin: 20px 0 10px 15px; font-size: 11px; color: #94a3b8; font-weight: bold; letter-spacing: 1px;">SYSTEM CONTROL</div>
            <a href="{{ route('owner.admins.index') }}">🔑 Kelola Akun Admin</a>
            <a href="{{ route('owner.settings.index') }}">⚙️ Pengaturan Jam Kerja</a>
        @else
            <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.index') ? 'active' : '' }}">
                👥 Karyawan
            </a>
        @endif
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