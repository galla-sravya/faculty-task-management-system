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
<body class="d-flex flex-column align-items-center justify-content-center min-vh-100 py-5 px-3" style="background-color: var(--bg);">

    <!-- Centered Login Logo Image -->
    <div class="mb-4 text-center">
        <a href="/" class="text-decoration-none">
            <img src="https://accounts.psgitech.ac.in/resources/j5fo1/login/laudea/img/logo_psgcas_black1.png"
                 alt="PSG iTech Logo"
                 style="height: 55px; max-width: 320px; object-fit: contain;"
                 onerror="this.onerror=null; this.src='{{ asset('images/psgitech-logo-white.png') }}';">
        </a>
    </div>

    <!-- Centered White Guest Card -->
    <div class="card bg-white shadow-sm border-0 w-100" style="max-width: 440px; border-radius: var(--radius, 8px);">
        <div class="card-body p-4 p-sm-5">
            {{ $slot }}
        </div>
    </div>

    <div class="mt-4 text-center text-muted small">
        &copy; {{ date('Y') }} PSG Institute of Technology and Applied Research. All rights reserved.
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
