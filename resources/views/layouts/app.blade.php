<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PSG iTech Activity Monitoring System') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- PSG Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/psg-theme.css') }}?v={{ filemtime(public_path('css/psg-theme.css')) }}">
    
    <!-- Voice Input CSS -->
    <link rel="stylesheet" href="{{ asset('css/voice-input.css') }}?v={{ filemtime(public_path('css/voice-input.css')) }}">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar + Top Header -->
    @include('layouts.navigation')

    <!-- Main Content Wrapper -->
    <div class="psg-content-wrapper" id="psgContentWrapper">
        <main class="w-100 mx-auto px-3 px-md-4 py-4" style="max-width:1400px; min-width:0;">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Modern Top-Right Toast Notification Container -->
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090; max-width: 90vw;">
        @if(session('success'))
            @php
                $isTaskCreated = Str::contains(session('success'), ['Task created', 'task created', 'Task Created', 'NBA Task created']);
                $toastTitle = $isTaskCreated ? 'Task Created Successfully' : 'Success';
                $toastMsg = $isTaskCreated 
                    ? 'The task has been assigned successfully. Notifications have been sent to the selected faculty member(s). If collaborators are selected, they have also been notified.' 
                    : session('success');
            @endphp
            <div id="appSuccessToast" class="toast custom-toast show border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="background: #ffffff; border-radius: 12px; overflow: hidden; min-width: 350px; max-width: 440px; border-left: 5px solid #2e7d32 !important;">
                <div class="p-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="toast-icon-bg text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #e8f5e9 !important;">
                            <i class="bi bi-check-circle-fill fs-5" style="color: #2e7d32;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">{{ $toastTitle }}</h6>
                                <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close" style="font-size: 0.75rem;"></button>
                            </div>
                            <p class="mb-2 text-secondary small" style="font-size: 0.83rem; line-height: 1.45;">
                                {{ $toastMsg }}
                            </p>
                            @if($isTaskCreated)
                                <div class="d-flex align-items-center gap-2 mt-2 pt-1">
                                    <a href="{{ auth()->user()->isHod() ? route('hod.tasks.index') : (auth()->user()->isNbaCoordinator() ? route('nba.tasks.index') : route('faculty.tasks.index')) }}" class="btn btn-sm btn-success px-3 py-1 fw-semibold shadow-sm" style="font-size: 0.78rem; background-color: #2e7d32; border: none;">
                                        <i class="bi bi-list-task me-1"></i> View Tasks
                                    </a>
                                    <a href="{{ auth()->user()->isHod() ? route('hod.tasks.create') : (auth()->user()->isNbaCoordinator() ? route('nba.tasks.create') : '#') }}" class="btn btn-sm btn-outline-secondary px-3 py-1 fw-medium" style="font-size: 0.78rem;">
                                        <i class="bi bi-plus-circle me-1"></i> Create Another Task
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="toast-progress-bar" style="height: 3.5px; width: 100%; animation: toastProgress 4.5s linear forwards; background-color: #2e7d32 !important;"></div>
            </div>
        @endif

        @if(session('error'))
            <div id="appErrorToast" class="toast custom-toast show border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="background: #ffffff; border-radius: 12px; overflow: hidden; min-width: 350px; max-width: 440px; border-left: 5px solid #d32f2f !important;">
                <div class="p-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="toast-icon-bg text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #ffebee !important;">
                            <i class="bi bi-exclamation-triangle-fill fs-5" style="color: #d32f2f;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">Task Could Not Be Created</h6>
                                <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close" style="font-size: 0.75rem;"></button>
                            </div>
                            <p class="mb-0 text-secondary small" style="font-size: 0.83rem; line-height: 1.45;">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="toast-progress-bar" style="height: 3.5px; width: 100%; animation: toastProgress 5s linear forwards; background-color: #d32f2f !important;"></div>
            </div>
        @endif
    </div>

    <style>
        .custom-toast {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
            animation: toastFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes toastFadeIn {
            from { opacity: 0; transform: translateY(-12px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .collapse-icon {
            display: inline-block;
            transition: transform 0.25s ease-in-out;
        }
        [aria-expanded="true"] .collapse-icon {
            transform: rotate(90deg);
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successToast = document.getElementById('appSuccessToast');
            if (successToast) {
                setTimeout(function () {
                    const bsToast = bootstrap.Toast.getOrCreateInstance(successToast);
                    bsToast.hide();
                }, 4500);
            }
            const errorToast = document.getElementById('appErrorToast');
            if (errorToast) {
                setTimeout(function () {
                    const bsToast = bootstrap.Toast.getOrCreateInstance(errorToast);
                    bsToast.hide();
                }, 5000);
            }
            document.querySelectorAll('.collapse').forEach(function(el) {
                if (!el.id) return;
                const key = 'collapse_state_' + el.id;
                if (sessionStorage.getItem(key) === 'open') {
                    const collapseInstance = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                    collapseInstance.show();
                }
                el.addEventListener('shown.bs.collapse', function() {
                    sessionStorage.setItem(key, 'open');
                });
                el.addEventListener('hidden.bs.collapse', function() {
                    sessionStorage.setItem(key, 'closed');
                });
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
    (function() {
        // Immediate restoration to avoid FOUC / layout shift
        const STORAGE_KEY = 'psg_sidebar_collapsed';
        if (window.innerWidth >= 768 && localStorage.getItem(STORAGE_KEY) === '1') {
            document.documentElement.classList.add('sidebar-collapsed');
            document.body?.classList.add('sidebar-collapsed');
            document.getElementById('psgSidebar')?.classList.add('collapsed');
            document.getElementById('psgTopbar')?.classList.add('sidebar-collapsed');
            document.getElementById('psgContentWrapper')?.classList.add('sidebar-collapsed');
        }
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('psgSidebar');
        const content = document.getElementById('psgContentWrapper');
        const topbar  = document.getElementById('psgTopbar');
        const toggle  = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        if (!sidebar || !toggle) return;

        const STORAGE_KEY = 'psg_sidebar_collapsed';
        const isMobile = () => window.innerWidth < 768;

        function toggleSidebar() {
            if (isMobile()) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                const collapsed = sidebar.classList.toggle('collapsed');
                content.classList.toggle('sidebar-collapsed', collapsed);
                topbar.classList.toggle('sidebar-collapsed', collapsed);
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            }
        }

        toggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-mobile-open');
        });

        // Handle resize
        let prevMobile = isMobile();
        window.addEventListener('resize', function() {
            const nowMobile = isMobile();
            if (prevMobile !== nowMobile) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-mobile-open');
                if (nowMobile) {
                    sidebar.classList.remove('collapsed');
                    content.classList.remove('sidebar-collapsed');
                    topbar.classList.remove('sidebar-collapsed');
                    document.body.classList.remove('sidebar-collapsed');
                    document.documentElement.classList.remove('sidebar-collapsed');
                } else if (localStorage.getItem(STORAGE_KEY) === '1') {
                    sidebar.classList.add('collapsed');
                    content.classList.add('sidebar-collapsed');
                    topbar.classList.add('sidebar-collapsed');
                    document.body.classList.add('sidebar-collapsed');
                    document.documentElement.classList.add('sidebar-collapsed');
                }
                prevMobile = nowMobile;
            }
        });
    });
    </script>

    <!-- Voice Input JS (Speech-to-Text for all textareas) -->
    <script src="{{ asset('js/voice-input.js') }}?v={{ filemtime(public_path('js/voice-input.js')) }}"></script>
    
    <!-- Task Table Filter JS -->
    <script src="{{ asset('js/task-table-filter.js') }}?v={{ filemtime(public_path('js/task-table-filter.js')) }}"></script>

    @yield('scripts')
    <script src="{{ asset('js/workload-warning.js') }}"></script>

    <!-- Global Actions Dropdown Portal Container -->
    <div id="adminActionsPortal" class="admin-actions-portal shadow-lg border rounded-3 bg-white" style="display: none; position: fixed; z-index: 1095; min-width: 190px; max-height: calc(100vh - 16px); overflow-y: auto;"></div>

    <style>
        .admin-actions-portal .dropdown-item:hover {
            background-color: #f8f9fa !important;
        }
        .admin-actions-portal .dropdown-item.text-danger:hover {
            background-color: #fee2e2 !important;
        }
    </style>
    <script>
        function copyCredential(btn, text) {
            navigator.clipboard.writeText(text).then(() => {
                const icon = btn.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'bi bi-check2 text-success';
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            });
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const portal = document.getElementById('adminActionsPortal');
        let currentActiveBtn = null;

        function closePortal() {
            if (portal) {
                portal.style.display = 'none';
                portal.innerHTML = '';
            }
            if (currentActiveBtn) {
                currentActiveBtn.classList.remove('active');
                currentActiveBtn = null;
            }
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.admin-actions-btn, .hod-actions-btn');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();

                const actionsId = btn.getAttribute('data-actions-id') || btn.getAttribute('data-hod-id');
                const template = document.getElementById('adminActionsTemplate' + actionsId) || document.getElementById('hodActionsMenuTemplate' + actionsId);
                if (!template) return;

                if (currentActiveBtn === btn) {
                    closePortal();
                    return;
                }

                closePortal();

                portal.innerHTML = template.innerHTML;
                portal.style.display = 'block';
                portal.style.visibility = 'hidden';

                const btnRect = btn.getBoundingClientRect();
                const portalHeight = portal.offsetHeight;
                const portalWidth = portal.offsetWidth;

                portal.style.visibility = '';

                const spaceBelow = window.innerHeight - btnRect.bottom;
                const spaceAbove = btnRect.top;

                let left = btnRect.right - portalWidth;
                if (left < 8) left = 8;
                if (left + portalWidth > window.innerWidth - 8) left = window.innerWidth - portalWidth - 8;
                portal.style.left = left + 'px';

                if (spaceBelow >= portalHeight || spaceBelow >= spaceAbove) {
                    portal.style.top = (btnRect.bottom + 4) + 'px';
                } else {
                    portal.style.top = Math.max(8, btnRect.top - portalHeight - 4) + 'px';
                }

                currentActiveBtn = btn;
                btn.classList.add('active');
                return;
            }

            if (portal && portal.style.display !== 'none') {
                if (!portal.contains(e.target)) {
                    closePortal();
                }
            }
        });

        if (portal) {
            portal.addEventListener('click', function (e) {
                const modalBtn = e.target.closest('[data-bs-toggle="modal"]');
                if (modalBtn) {
                    const targetSelector = modalBtn.getAttribute('data-bs-target');
                    closePortal();
                    if (targetSelector) {
                        const modalEl = document.querySelector(targetSelector);
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        }
                    }
                    return;
                }

                const submitBtn = e.target.closest('button[type="submit"]');
                if (submitBtn) {
                    const form = submitBtn.closest('form');
                    if (form) {
                        closePortal();
                        const onsubmitAttr = form.getAttribute('onsubmit');
                        if (onsubmitAttr) {
                            const confirmMatch = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                            const confirmMsg = confirmMatch ? confirmMatch[1] : 'Are you sure you want to proceed?';
                            if (!confirm(confirmMsg)) {
                                e.preventDefault();
                                return;
                            }
                        }
                    }
                }
            });
        }

        window.addEventListener('scroll', function () {
            if (portal && portal.style.display !== 'none') {
                closePortal();
            }
        }, { capture: true, passive: true });

        window.addEventListener('resize', closePortal, { passive: true });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closePortal();
            }
        });
    });
    </script>
</body>
</html>
