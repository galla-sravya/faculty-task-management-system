<!-- ═══════════════════════════════════════════════════════════ -->
<!-- PSG iTech — Collapsible Sidebar (ChatGPT / Notion style) -->
<!-- ═══════════════════════════════════════════════════════════ -->
@auth
<aside class="psg-sidebar" id="psgSidebar">
    <!-- ── Logo Section ── -->
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none gap-2 w-100">
            <img src="{{ asset('images/psgitech-logo-white.png') }}" alt="PSG iTech" class="sidebar-logo-full">
            <img src="{{ asset('images/psgitech-logo-white.png') }}" alt="PSG" class="sidebar-logo-icon" style="display:none;">
        </a>
    </div>

    <!-- ── Navigation Links ── -->
    <nav class="sidebar-nav flex-grow-1">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i><span class="sidebar-label">Dashboard</span>
            </a>

            <div class="sidebar-heading px-3 pt-3 pb-1 text-muted text-uppercase fw-bold" style="font-size:0.68rem; letter-spacing:0.8px;">Accounts</div>

            <a href="{{ route('admin.hods.index') }}" class="sidebar-link {{ request()->routeIs('admin.hods.*') ? 'active' : '' }}" title="HOD Accounts">
                <i class="bi bi-person-badge"></i><span class="sidebar-label">HOD Accounts</span>
            </a>
            <a href="{{ route('admin.nba-coordinators.index') }}" class="sidebar-link {{ request()->routeIs('admin.nba-coordinators.*') ? 'active' : '' }}" title="NBA Coordinator Accounts">
                <i class="bi bi-person-workspace"></i><span class="sidebar-label">NBA Coordinators</span>
            </a>
            <a href="{{ route('admin.dynamic-coordinators.index') }}" class="sidebar-link {{ request()->routeIs('admin.dynamic-coordinators.*') ? 'active' : '' }}" title="Dynamic Coordinator Accounts">
                <i class="bi bi-person-gear"></i><span class="sidebar-label">Dynamic Coordinators</span>
            </a>

            <div class="sidebar-heading px-3 pt-3 pb-1 text-muted text-uppercase fw-bold" style="font-size:0.68rem; letter-spacing:0.8px;">Organization</div>

            <a href="{{ route('admin.departments.index') }}" class="sidebar-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" title="Departments">
                <i class="bi bi-building"></i><span class="sidebar-label">Departments</span>
            </a>
            <a href="{{ route('admin.coordinator-types.index') }}" class="sidebar-link {{ request()->routeIs('admin.coordinator-types.*') ? 'active' : '' }}" title="Role Types">
                <i class="bi bi-tags"></i><span class="sidebar-label">Role Types</span>
            </a>
        @elseif(auth()->user()->isHod())
            <a href="{{ route('hod.dashboard') }}" class="sidebar-link {{ request()->routeIs('hod.dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i><span class="sidebar-label">Dashboard</span>
            </a>
            <a href="{{ route('hod.tasks.index') }}" class="sidebar-link {{ request()->routeIs('hod.tasks.*') ? 'active' : '' }}" title="Tasks">
                <i class="bi bi-list-task"></i><span class="sidebar-label">Tasks</span>
            </a>
            <a href="{{ route('hod.meetings.index') }}" class="sidebar-link {{ request()->routeIs('hod.meetings.*') ? 'active' : '' }}" title="Meetings">
                <i class="bi bi-calendar-event"></i><span class="sidebar-label">Meetings</span>
            </a>
            <a href="{{ route('hod.faculty.index') }}" class="sidebar-link {{ request()->routeIs('hod.faculty.*') ? 'active' : '' }}" title="Faculty">
                <i class="bi bi-people"></i><span class="sidebar-label">Faculty</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="sidebar-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}" title="Calendar">
                <i class="bi bi-calendar3"></i><span class="sidebar-label">Calendar</span>
            </a>
            <a href="{{ route('hod.reports.index') }}" class="sidebar-link {{ request()->routeIs('hod.reports.*') ? 'active' : '' }}" title="Reports">
                <i class="bi bi-bar-chart-line"></i><span class="sidebar-label">Reports</span>
            </a>
            <a href="{{ route('hod.tasks.archived') }}" class="sidebar-link {{ request()->routeIs('hod.tasks.archived') ? 'active' : '' }}" title="Archived Tasks">
                <i class="bi bi-archive"></i><span class="sidebar-label">Archived Tasks</span>
            </a>
        @elseif(auth()->user()->isNbaCoordinator())
            <a href="{{ route('nba.dashboard') }}" class="sidebar-link {{ request()->routeIs('nba.dashboard') ? 'active' : '' }}" title="NBA Dashboard">
                <i class="bi bi-speedometer2"></i><span class="sidebar-label">NBA Dashboard</span>
            </a>
            <a href="{{ route('nba.tasks.index') }}" class="sidebar-link {{ request()->routeIs('nba.tasks.*') ? 'active' : '' }}" title="NBA Tasks">
                <i class="bi bi-list-task"></i><span class="sidebar-label">NBA Tasks</span>
            </a>
            <a href="{{ route('nba.meetings.index') }}" class="sidebar-link {{ request()->routeIs('nba.meetings.*') ? 'active' : '' }}" title="NBA Meetings">
                <i class="bi bi-calendar-event"></i><span class="sidebar-label">NBA Meetings</span>
            </a>
            <a href="{{ route('nba.faculty.index') }}" class="sidebar-link {{ request()->routeIs('nba.faculty.*') ? 'active' : '' }}" title="Faculty">
                <i class="bi bi-people"></i><span class="sidebar-label">Faculty</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="sidebar-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}" title="Calendar">
                <i class="bi bi-calendar3"></i><span class="sidebar-label">Calendar</span>
            </a>
            <a href="{{ route('nba.reports.index') }}" class="sidebar-link {{ request()->routeIs('nba.reports.*') ? 'active' : '' }}" title="NBA Reports">
                <i class="bi bi-bar-chart-line"></i><span class="sidebar-label">NBA Reports</span>
            </a>
            <a href="{{ route('nba.tasks.archived') }}" class="sidebar-link {{ request()->routeIs('nba.tasks.archived') ? 'active' : '' }}" title="Archived Tasks">
                <i class="bi bi-archive"></i><span class="sidebar-label">Archived Tasks</span>
            </a>
        @elseif(auth()->user()->isCoordinator())
            <a href="{{ route('coordinator.dashboard') }}" class="sidebar-link {{ request()->routeIs('coordinator.dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i><span class="sidebar-label">Dashboard</span>
            </a>
            <a href="{{ route('coordinator.tasks.index') }}" class="sidebar-link {{ request()->routeIs('coordinator.tasks.*') ? 'active' : '' }}" title="Tasks">
                <i class="bi bi-list-task"></i><span class="sidebar-label">Tasks</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="sidebar-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}" title="Calendar">
                <i class="bi bi-calendar3"></i><span class="sidebar-label">Calendar</span>
            </a>
        @else
            <a href="{{ route('faculty.dashboard') }}" class="sidebar-link {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i><span class="sidebar-label">Dashboard</span>
            </a>
            <a href="{{ route('faculty.tasks.index') }}" class="sidebar-link {{ request()->routeIs('faculty.tasks.*') ? 'active' : '' }}" title="My Tasks">
                <i class="bi bi-list-task"></i><span class="sidebar-label">My Tasks</span>
            </a>
            <a href="{{ route('faculty.meetings.index') }}" class="sidebar-link {{ request()->routeIs('faculty.meetings.*') ? 'active' : '' }}" title="Meetings">
                <i class="bi bi-calendar-event"></i><span class="sidebar-label">Meetings</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="sidebar-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}" title="Calendar">
                <i class="bi bi-calendar3"></i><span class="sidebar-label">Calendar</span>
            </a>
            <a href="{{ route('faculty.reports.index') }}" class="sidebar-link {{ request()->routeIs('faculty.reports.*') ? 'active' : '' }}" title="Reports">
                <i class="bi bi-bar-chart-line"></i><span class="sidebar-label">Reports</span>
            </a>
        @endif
    </nav>

    <!-- ── Bottom Section: Settings & Logout ── -->
    <div class="sidebar-footer">
        <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" title="Settings">
            <i class="bi bi-gear"></i><span class="sidebar-label">Settings</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sidebar-link sidebar-logout-btn" title="Logout">
                <i class="bi bi-box-arrow-left"></i><span class="sidebar-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

<!-- ── Mobile Overlay ── -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── Compact Top Header ── -->
<header class="psg-topbar" id="psgTopbar">
    <div class="topbar-left">
        <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="topbar-title">
            @php
                $pageTitle = 'Dashboard';
                if (request()->routeIs('*.dashboard')) $pageTitle = 'Dashboard';
                elseif (request()->routeIs('admin.hods.*')) $pageTitle = 'HOD Accounts';
                elseif (request()->routeIs('admin.nba-coordinators.*')) $pageTitle = 'NBA Coordinator Accounts';
                elseif (request()->routeIs('admin.dynamic-coordinators.*')) $pageTitle = 'Dynamic Coordinator Accounts';
                elseif (request()->routeIs('admin.departments.show')) $pageTitle = 'Department Details & Faculty';
                elseif (request()->routeIs('admin.departments.*')) $pageTitle = 'Departments';
                elseif (request()->routeIs('admin.coordinator-types.*')) $pageTitle = 'Role Types';
                elseif (request()->routeIs('*.tasks.create')) $pageTitle = 'Create Task';
                elseif (request()->routeIs('*.tasks.show')) $pageTitle = 'Task Details';
                elseif (request()->routeIs('*.tasks.edit')) $pageTitle = 'Edit Task';
                elseif (request()->routeIs('*.tasks.*')) $pageTitle = 'Tasks';
                elseif (request()->routeIs('*.meetings.create')) $pageTitle = 'Schedule Meeting';
                elseif (request()->routeIs('*.meetings.show')) $pageTitle = 'Meeting Details';
                elseif (request()->routeIs('*.meetings.completeForm')) $pageTitle = 'Complete Meeting';
                elseif (request()->routeIs('*.meetings.*')) $pageTitle = 'Meetings';
                elseif (request()->routeIs('hod.faculty.performance')) $pageTitle = 'Faculty Performance';
                elseif (request()->routeIs('*.faculty.*')) $pageTitle = 'Faculty';
                elseif (request()->routeIs('*.reports.show')) $pageTitle = 'Report Details';
                elseif (request()->routeIs('*.reports.*')) $pageTitle = 'Reports';
                elseif (request()->routeIs('profile.edit')) $pageTitle = 'Profile Settings';
                elseif (request()->routeIs('notifications.*')) $pageTitle = 'Notifications';
            @endphp
            {{ $pageTitle }}
        </h1>
    </div>
    <div class="topbar-right">
        <!-- Global Search Bar -->
        <form action="{{ route('global.search') }}" method="GET" class="d-none d-md-flex align-items-center me-2">
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <input type="text" name="q" class="form-control border-end-0 bg-light" placeholder="Search tasks, faculty..." style="font-size: 0.78rem;" value="{{ request('q') }}">
                <button class="btn btn-outline-secondary border-start-0 bg-light" type="submit" style="font-size: 0.78rem;">
                    <i class="bi bi-search text-muted"></i>
                </button>
            </div>
        </form>

        <!-- Notifications Bell -->
        <a href="{{ route('notifications.index') }}" class="topbar-icon-btn position-relative" title="Notifications">
            <i class="bi bi-bell"></i>
            @if(auth()->user()->notifications()->where('is_read', false)->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">
                    {{ auth()->user()->notifications()->where('is_read', false)->count() }}
                </span>
            @endif
        </a>
        <!-- User Profile -->
        <div class="dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle topbar-user" href="#" role="button" id="topbarUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="rounded-circle shadow-sm object-fit-cover" style="width:32px;height:32px;border:2px solid var(--navy);">
                <span class="topbar-user-name">{{ auth()->user()->name }}</span>
                <span class="badge bg-light border ms-1" style="color:var(--navy);font-size:0.65rem;">{{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border" aria-labelledby="topbarUserMenu" style="border-color:var(--border);">
                <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-1"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger d-flex align-items-center gap-2" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
@endauth
