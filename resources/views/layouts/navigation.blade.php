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
        @if(auth()->user()->isHod())
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
            <a href="{{ route('hod.reports.index') }}" class="sidebar-link {{ request()->routeIs('hod.reports.*') ? 'active' : '' }}" title="Reports">
                <i class="bi bi-bar-chart-line"></i><span class="sidebar-label">Reports</span>
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
                <span class="badge bg-light border ms-1" style="color:var(--navy);font-size:0.65rem;">{{ ucfirst(auth()->user()->role) }}</span>
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
