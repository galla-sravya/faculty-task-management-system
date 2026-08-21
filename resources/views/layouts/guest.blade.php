<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PSG iTech Activity Monitoring System') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- PSG Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/psg-theme.css') }}?v={{ filemtime(public_path('css/psg-theme.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/voice-input.css') }}?v={{ filemtime(public_path('css/voice-input.css')) }}">
</head>
<body class="d-flex flex-column align-items-center justify-content-center min-vh-100 py-2 px-3" style="background-color: var(--bg);">

    <!-- Centered Login Logo Image -->
    <div class="mb-3 text-center">
        <a href="/" class="text-decoration-none">
            <img src="https://accounts.psgitech.ac.in/resources/j5fo1/login/laudea/img/logo_psgcas_black1.png"
                 alt="PSG iTech Logo"
                 style="height: 48px; max-width: 300px; object-fit: contain;"
                 onerror="this.onerror=null; this.src='{{ asset('images/psgitech-logo-white.png') }}';">
        </a>
    </div>

    <!-- Centered White Guest Card -->
    <div class="card bg-white shadow-lg border-0 w-100" style="max-width: 440px; border-radius: var(--radius, 8px);">
        <div class="card-body p-4">
            {{ $slot }}
        </div>
    </div>

    <!-- Developer Credits Footer -->
    <footer class="mt-3 text-center" style="max-width: 440px; width: 100%;">
        <div style="height: 2px; background: linear-gradient(90deg, transparent, var(--navy, #1b3a5c), transparent); margin-bottom: 0.75rem;"></div>

        <p class="mb-1" style="font-size: 0.85rem; color: #4a5568; font-style: italic;">
            <i class="bi bi-buildings me-1"></i>from the labs of
            <span style="font-weight: 700; color: var(--navy, #1b3a5c); font-style: normal; font-size: 0.95rem;">SDC</span>
        </p>

        <p class="mb-0" style="font-size: 0.75rem; color: #6c757d; font-weight: 700;">
            DEVELOPED BY
        </p>
        <p class="mb-2" style="font-size: 0.8rem; color: var(--navy, #1b3a5c); font-weight: 700;">
            Deeksha S &middot; Amala Arlyn A &middot; Galla Sravya
        </p>

        <p class="mb-0" style="font-size: 0.7rem; color: #495057;">
            &copy; {{ date('Y') }} PSG Institute of Technology and Applied Research. All rights reserved.
        </p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

