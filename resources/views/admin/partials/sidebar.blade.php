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

        @can('access-admin')
            <div style="margin: 15px 0 5px 10px; font-size: 11px; color: #94a3b8; font-weight: bold; letter-spacing: 1px;">OPERASIONAL</div>
            
            <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}">
                📊 Log Kehadiran Hari Ini
            </a>
            
            <a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}" style="display: flex; justify-content: space-between; align-items: center;">
                <span>📝 Persetujuan Cuti</span>
                
                @php
                    $pendingCount = \App\Models\Attendance::where('approval_status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span style="background: #ef4444; color: white; font-size: 11px; padding: 2px 6px; border-radius: 10px; font-weight: bold;">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.shifts.index') }}" class="{{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                📅 Atur Shift Staff CS
            </a>


            {{-- 3. MENU DUO LAPORAN BARU (MANAJEMEN REKAP) --}}
            <div style="margin: 20px 0 5px 10px; font-size: 11px; color: #94a3b8; font-weight: bold; letter-spacing: 1px;">LAPORAN REKAP</div>

            <a href="{{ route('admin.reports.staff') }}" class="{{ request()->routeIs('admin.reports.staff') ? 'active' : '' }}">
                🗂️ Berkas Laporan Per Staff
            </a>

            <a href="{{ route('admin.reports.attendance') }}" class="{{ request()->routeIs('admin.reports.attendance') ? 'active' : '' }}">
                📉 Ringkasan Kehadiran & Potongan
            </a>
        @endcan

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