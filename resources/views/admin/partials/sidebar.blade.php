<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Attendance<span>Pro</span></h2>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i>📊</i> Dashboard
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
            <i>📅</i> Attendance Logs
        </a>
        <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
            <i>👥</i> Employees
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <p>{{ auth()->user()->name }}</p>
            <span>Administrator</span>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</aside>