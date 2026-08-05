<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PSG iTech - Activity Monitoring System</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- PSG Theme CSS -->
    @vite(['resources/css/psg-theme.css'])
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: var(--bg);">

    <!-- Header Navigation -->
    <header class="bg-white border-bottom py-3 px-4 shadow-sm">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <a href="/" class="text-decoration-none">
                <x-application-logo />
            </a>

            <div>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn text-white fw-semibold px-4 shadow-sm" style="background-color: var(--navy);">
                        Go to Dashboard <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn text-white fw-semibold px-4 shadow-sm" style="background-color: var(--navy);">
                        Log In <i class="bi bi-box-arrow-in-right ms-1"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container my-auto">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 text-center text-lg-start">
                    <span class="badge rounded-pill px-3 py-2 mb-3 shadow-sm" style="background-color: rgba(18, 39, 90, 0.08); color: var(--navy); font-weight: 600;">
                        <i class="bi bi-shield-check me-1"></i> Institutional Activity Portal
                    </span>
                    <h1 class="display-5 fw-bold mb-3" style="color: var(--navy); line-height: 1.2;">
                        PSG Institute of Technology and Applied Research
                    </h1>
                    <p class="lead text-muted mb-4">
                        Streamlined departmental activity tracking, faculty task assignments, progress analytics, and meeting schedules in one central platform.
                    </p>

                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-lg text-white fw-semibold px-4 shadow" style="background-color: var(--navy);">
                                Access Dashboard <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-lg text-white fw-semibold px-4 shadow" style="background-color: var(--navy);">
                                Log In to Account <i class="bi bi-box-arrow-in-right ms-1"></i>
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card bg-white shadow border-0 p-4" style="border-radius: var(--radius, 12px);">
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border h-100">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-white"
                                         style="width: 44px; height: 44px; background-color: var(--navy);">
                                        <i class="bi bi-list-task fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Task Management</h6>
                                    <p class="small text-muted mb-0">Assign tasks with priorities, set deadlines, and track real-time progress.</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border h-100">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-white"
                                         style="width: 44px; height: 44px; background-color: var(--maroon);">
                                        <i class="bi bi-speedometer2 fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Faculty Analytics</h6>
                                    <p class="small text-muted mb-0">Visual progress rings, status charts, and individual performance metrics.</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border h-100">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-white"
                                         style="width: 44px; height: 44px; background-color: var(--gold);">
                                        <i class="bi bi-calendar-event fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Department Meetings</h6>
                                    <p class="small text-muted mb-0">Schedule meetings, invite faculty, attach agendas, and log completion.</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border h-100">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 text-white"
                                         style="width: 44px; height: 44px; background-color: var(--success);">
                                        <i class="bi bi-bell fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Automated Alerts</h6>
                                    <p class="small text-muted mb-0">Instant email notifications for task assignments and meeting invites.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted small">
        <div class="container">
            &copy; {{ date('Y') }} PSG Institute of Technology and Applied Research. All rights reserved.
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
