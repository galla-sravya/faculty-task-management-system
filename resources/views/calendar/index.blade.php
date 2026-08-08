@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Department Task & Meeting Calendar</h1>
        <p class="text-muted small mb-0">Overview of task deadlines, assignments, and scheduled department meetings</p>
    </div>
</div>

<!-- Google Calendar Embed Section -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold m-0" style="color: var(--navy);">
                <i class="bi bi-google me-2" style="color: #4285F4;"></i>My Google Calendar
            </h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1" id="gcal-settings-btn"
                        style="background-color: #f8f9fa; color: #495057; border: 1px solid #dee2e6; font-size: 0.82rem;">
                    <i class="bi bi-gear me-1"></i> Configure
                </button>
                <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1" id="gcal-toggle-btn"
                        style="background-color: var(--navy, #12275a); color: #fff; border: 1px solid var(--navy, #12275a); font-size: 0.82rem;">
                    <i class="bi bi-eye me-1" id="gcal-toggle-icon"></i>
                    <span id="gcal-toggle-text">Show Calendar</span>
                </button>
            </div>
        </div>

        <!-- Google Calendar Iframe Container -->
        <div id="gcal-container" class="d-none">
            <div id="gcal-iframe-wrapper" class="d-none">
                <div class="border rounded-3 overflow-hidden" style="border-color: #dee2e6 !important;">
                    <iframe id="gcal-iframe" src="" style="border: 0; width: 100%; height: 600px;" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
            <div id="gcal-setup-prompt" class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px; background: linear-gradient(135deg, #4285F4 0%, #34A853 50%, #FBBC05 75%, #EA4335 100%); opacity: 0.15;">
                </div>
                <i class="bi bi-calendar2-plus fs-1 d-block mb-3" style="color: #4285F4;"></i>
                <h6 class="fw-bold mb-2" style="color: var(--navy);">Connect Your Google Calendar</h6>
                <p class="text-muted small mb-3" style="max-width: 450px; margin: 0 auto;">
                    View your personal Google Calendar right here alongside your task deadlines. To get started:
                </p>
                <div class="text-start mx-auto small text-muted mb-4" style="max-width: 420px;">
                    <div class="mb-2"><span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; font-size: 0.7rem; line-height: 14px;">1</span> Open <a href="https://calendar.google.com/calendar/r/settings" target="_blank" class="text-decoration-none fw-semibold" style="color: #4285F4;">Google Calendar Settings</a></div>
                    <div class="mb-2"><span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; font-size: 0.7rem; line-height: 14px;">2</span> Click your calendar under <strong>"Settings for my calendars"</strong></div>
                    <div class="mb-2"><span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; font-size: 0.7rem; line-height: 14px;">3</span> Scroll to <strong>"Integrate calendar"</strong> section</div>
                    <div class="mb-2"><span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; font-size: 0.7rem; line-height: 14px;">4</span> Copy the <strong>"Public URL to this calendar"</strong> or <strong>"Embed code"</strong></div>
                    <div><span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; font-size: 0.7rem; line-height: 14px;">5</span> Click <strong>Configure</strong> above and paste it in</div>
                </div>
                <button type="button" class="btn btn-sm px-4 py-2 fw-semibold text-white shadow-sm" id="gcal-setup-configure-btn"
                        style="background-color: #4285F4; border-radius: 8px; font-size: 0.85rem;">
                    <i class="bi bi-gear me-1"></i> Configure Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Google Calendar Settings Modal -->
<div class="modal fade" id="gcalSettingsModal" tabindex="-1" aria-labelledby="gcalSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-bottom" style="border-color: #f0f0f0 !important;">
                <h6 class="modal-title fw-bold" id="gcalSettingsModalLabel" style="color: var(--navy);">
                    <i class="bi bi-google me-2" style="color: #4285F4;"></i>Google Calendar Settings
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <label for="gcal-url-input" class="form-label fw-semibold small" style="color: var(--navy);">Google Calendar Embed URL or Public URL</label>
                <input type="text" class="form-control" id="gcal-url-input"
                       placeholder="Paste your Google Calendar URL or embed code here..."
                       style="font-size: 0.85rem; border-radius: 8px; border-color: #dee2e6;">
                <div class="form-text mt-2 small">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Paste the <strong>embed URL</strong> from your Google Calendar settings, or the entire <code>&lt;iframe&gt;</code> embed code — we'll extract the URL automatically.
                </div>

                <div class="mt-3 p-3 rounded-3" style="background-color: #f8f9fa;">
                    <div class="small fw-semibold mb-2" style="color: var(--navy);">Example URLs:</div>
                    <code class="d-block small text-break mb-1" style="font-size: 0.72rem;">https://calendar.google.com/calendar/embed?src=your_email@gmail.com</code>
                    <code class="d-block small text-break" style="font-size: 0.72rem;">https://calendar.google.com/calendar/u/0/r</code>
                </div>
            </div>
            <div class="modal-footer border-top" style="border-color: #f0f0f0 !important;">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="gcal-clear-btn" style="font-size: 0.82rem;">
                    <i class="bi bi-trash me-1"></i> Clear
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-4 fw-semibold text-white" id="gcal-save-btn"
                        style="background-color: var(--navy, #12275a); font-size: 0.82rem;">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold m-0" style="color: var(--navy);">
                <i class="bi bi-calendar3 me-2" style="color: var(--gold);"></i>Calendar Schedule
            </h5>
        </div>

        <!-- Interactive Event Type Filter Chips -->
        <div class="d-flex align-items-center flex-wrap gap-2 mb-4" id="calendar-filter-chips">
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn active" data-filter="all">
                <i class="bi bi-grid-fill me-1"></i> All Events
                <span class="badge rounded-pill bg-white text-dark ms-1 filter-badge" id="count-all">0</span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn" data-filter="tasks">
                <i class="bi bi-list-task me-1"></i> Tasks
                <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-tasks">0</span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn" data-filter="meetings">
                <i class="bi bi-calendar-event me-1"></i> Meetings
                <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-meetings">0</span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn" data-filter="completed">
                <span class="filter-dot me-1" style="background:#2e7d32;"></span> Completed Tasks
                <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-completed">0</span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn" data-filter="in_progress">
                <span class="filter-dot me-1" style="background:#1565c0;"></span> In Progress Tasks
                <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-in_progress">0</span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn" data-filter="pending_review">
                <span class="filter-dot me-1" style="background:#7b1fa2;"></span> Pending Review
                <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-pending_review">0</span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn" data-filter="overdue">
                <span class="filter-dot me-1" style="background:#c62828;"></span> Overdue Tasks
                <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-overdue">0</span>
            </button>
        </div>

        <div class="table-responsive">
            <div class="list-group list-group-flush" id="calendar-events-container">
                @forelse($events->sortBy('start') as $event)
                    <div class="list-group-item px-3 py-3 border rounded mb-2 d-flex justify-content-between align-items-center bg-light calendar-event-item"
                         data-categories="{{ implode(' ', $event['categories'] ?? []) }}"
                         style="border-left: 5px solid {{ $event['color'] }} !important;">
                        <div>
                            <div class="fw-semibold text-dark mb-1" style="font-size: 0.95rem;">{{ $event['title'] }}</div>
                            <div class="text-muted small">
                                <i class="bi bi-calendar-event me-1"></i>Date: <strong>{{ Carbon\Carbon::parse($event['start'])->format('M d, Y') }}</strong>
                                @if(isset($event['venue']))
                                    · <i class="bi bi-geo-alt me-1 text-danger"></i>Venue: {{ $event['venue'] }}
                                @endif
                                @if(isset($event['status']))
                                    · <span class="badge bg-white text-dark border ms-1">{{ $event['status'] }}</span>
                                @endif
                                @if(!empty($event['overdue_label']))
                                    · <span class="badge bg-danger text-white ms-1" style="font-size: 0.65rem;">
                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $event['overdue_label'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ $event['url'] }}" class="btn btn-sm text-white fw-medium shadow-sm px-3" style="background-color: var(--navy); font-size: 0.8rem;">
                            View Details <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted" id="initial-empty-state">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                        No scheduled tasks or meetings found.
                    </div>
                @endforelse

                <!-- Filter Empty State -->
                <div class="text-center py-5 text-muted d-none" id="filter-empty-state">
                    <i class="bi bi-funnel fs-2 d-block mb-2 text-secondary"></i>
                    No events match the selected filters.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .calendar-filter-btn {
        background-color: #ffffff;
        color: #495057;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
    }
    .calendar-filter-btn:hover {
        background-color: #f8f9fa;
        border-color: #ced4da;
        transform: translateY(-1px);
    }
    .calendar-filter-btn.active {
        color: #ffffff !important;
        box-shadow: 0 3px 8px rgba(0,0,0,0.18);
        transform: translateY(-1px);
    }
    .calendar-filter-btn.active[data-filter="all"] {
        background-color: var(--navy, #12275a) !important;
        border-color: var(--navy, #12275a) !important;
    }
    .calendar-filter-btn.active[data-filter="tasks"] {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
    }
    .calendar-filter-btn.active[data-filter="meetings"] {
        background-color: #7b1fa2 !important;
        border-color: #7b1fa2 !important;
    }
    .calendar-filter-btn.active[data-filter="completed"] {
        background-color: #2e7d32 !important;
        border-color: #2e7d32 !important;
    }
    .calendar-filter-btn.active[data-filter="in_progress"] {
        background-color: #1565c0 !important;
        border-color: #1565c0 !important;
    }
    .calendar-filter-btn.active[data-filter="pending_review"] {
        background-color: #7b1fa2 !important;
        border-color: #7b1fa2 !important;
    }
    .calendar-filter-btn.active[data-filter="overdue"] {
        background-color: #c62828 !important;
        border-color: #c62828 !important;
    }
    .calendar-filter-btn.active .filter-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }
    .filter-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
    }
    #gcal-container {
        transition: all 0.3s ease-in-out;
    }
    #gcal-iframe-wrapper {
        animation: fadeInUp 0.3s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Google Calendar Embed Logic ──
    const gcalToggleBtn = document.getElementById('gcal-toggle-btn');
    const gcalToggleIcon = document.getElementById('gcal-toggle-icon');
    const gcalToggleText = document.getElementById('gcal-toggle-text');
    const gcalContainer = document.getElementById('gcal-container');
    const gcalIframeWrapper = document.getElementById('gcal-iframe-wrapper');
    const gcalSetupPrompt = document.getElementById('gcal-setup-prompt');
    const gcalIframe = document.getElementById('gcal-iframe');
    const gcalSettingsBtn = document.getElementById('gcal-settings-btn');
    const gcalSetupConfigureBtn = document.getElementById('gcal-setup-configure-btn');
    const gcalUrlInput = document.getElementById('gcal-url-input');
    const gcalSaveBtn = document.getElementById('gcal-save-btn');
    const gcalClearBtn = document.getElementById('gcal-clear-btn');

    const STORAGE_KEY = 'ftms_google_calendar_url';
    const TOGGLE_KEY = 'ftms_google_calendar_visible';

    function getStoredUrl() {
        return localStorage.getItem(STORAGE_KEY) || '';
    }

    function extractEmbedUrl(input) {
        if (!input) return '';
        input = input.trim();

        // Extract src from iframe embed code
        const srcMatch = input.match(/src=["']([^"']+)["']/);
        if (srcMatch) return srcMatch[1];

        // Already a URL
        if (input.startsWith('http')) return input;

        return input;
    }

    function updateGcalView() {
        const url = getStoredUrl();
        if (url) {
            gcalIframe.src = url;
            gcalIframeWrapper.classList.remove('d-none');
            gcalSetupPrompt.classList.add('d-none');
        } else {
            gcalIframe.src = '';
            gcalIframeWrapper.classList.add('d-none');
            gcalSetupPrompt.classList.remove('d-none');
        }
    }

    function toggleCalendar(show) {
        if (show) {
            gcalContainer.classList.remove('d-none');
            gcalToggleIcon.className = 'bi bi-eye-slash me-1';
            gcalToggleText.textContent = 'Hide Calendar';
            gcalToggleBtn.style.backgroundColor = '#6c757d';
            gcalToggleBtn.style.borderColor = '#6c757d';
            localStorage.setItem(TOGGLE_KEY, 'true');
            updateGcalView();
        } else {
            gcalContainer.classList.add('d-none');
            gcalToggleIcon.className = 'bi bi-eye me-1';
            gcalToggleText.textContent = 'Show Calendar';
            gcalToggleBtn.style.backgroundColor = 'var(--navy, #12275a)';
            gcalToggleBtn.style.borderColor = 'var(--navy, #12275a)';
            localStorage.setItem(TOGGLE_KEY, 'false');
        }
    }

    // Initialize toggle state from localStorage
    const savedVisible = localStorage.getItem(TOGGLE_KEY) === 'true';
    toggleCalendar(savedVisible);

    gcalToggleBtn.addEventListener('click', function () {
        const isVisible = !gcalContainer.classList.contains('d-none');
        toggleCalendar(!isVisible);
    });

    // Open settings modal
    function openSettingsModal() {
        gcalUrlInput.value = getStoredUrl();
        const modal = new bootstrap.Modal(document.getElementById('gcalSettingsModal'));
        modal.show();
    }

    gcalSettingsBtn.addEventListener('click', openSettingsModal);
    gcalSetupConfigureBtn.addEventListener('click', function () {
        toggleCalendar(true);
        openSettingsModal();
    });

    // Save URL
    gcalSaveBtn.addEventListener('click', function () {
        const raw = gcalUrlInput.value;
        const url = extractEmbedUrl(raw);
        if (url) {
            localStorage.setItem(STORAGE_KEY, url);
            updateGcalView();
        }
        bootstrap.Modal.getInstance(document.getElementById('gcalSettingsModal')).hide();
    });

    // Clear URL
    gcalClearBtn.addEventListener('click', function () {
        localStorage.removeItem(STORAGE_KEY);
        gcalUrlInput.value = '';
        updateGcalView();
        bootstrap.Modal.getInstance(document.getElementById('gcalSettingsModal')).hide();
    });

    // ── Calendar Filter Logic ──
    const filterContainer = document.getElementById('calendar-filter-chips');
    if (!filterContainer) return;

    const eventItems = document.querySelectorAll('.calendar-event-item');
    const filterEmptyState = document.getElementById('filter-empty-state');
    
    // Dynamic detection of extra event types not predefined
    const predefinedKeys = new Set(['all', 'tasks', 'meetings', 'completed', 'in_progress', 'pending_review', 'overdue']);
    const dynamicKeys = new Set();

    eventItems.forEach(item => {
        const cats = (item.dataset.categories || '').split(' ').filter(Boolean);
        cats.forEach(cat => {
            if (!predefinedKeys.has(cat)) {
                dynamicKeys.add(cat);
            }
        });
    });

    dynamicKeys.forEach(key => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm rounded-pill fw-semibold px-3 py-1.5 calendar-filter-btn';
        btn.dataset.filter = key;
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        btn.innerHTML = `<i class="bi bi-tag-fill me-1"></i> ${label} <span class="badge rounded-pill bg-light text-dark ms-1 filter-badge" id="count-${key}">0</span>`;
        filterContainer.appendChild(btn);
    });

    // Compute counts for each filter category
    function updateCounts() {
        const counts = { all: eventItems.length };
        eventItems.forEach(item => {
            const cats = (item.dataset.categories || '').split(' ').filter(Boolean);
            cats.forEach(cat => {
                counts[cat] = (counts[cat] || 0) + 1;
            });
        });

        document.querySelectorAll('.calendar-filter-btn').forEach(btn => {
            const filterKey = btn.dataset.filter;
            const badge = btn.querySelector('.filter-badge');
            if (badge) {
                badge.textContent = counts[filterKey] || 0;
            }
        });
    }

    updateCounts();

    // Multi-select active filters set
    let activeFilters = new Set(['all']);

    filterContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.calendar-filter-btn');
        if (!btn) return;

        const clickedFilter = btn.dataset.filter;

        if (clickedFilter === 'all') {
            activeFilters.clear();
            activeFilters.add('all');
        } else {
            activeFilters.delete('all');
            if (activeFilters.has(clickedFilter)) {
                activeFilters.delete(clickedFilter);
            } else {
                activeFilters.add(clickedFilter);
            }
            if (activeFilters.size === 0) {
                activeFilters.add('all');
            }
        }

        // Update button active states
        document.querySelectorAll('.calendar-filter-btn').forEach(b => {
            if (activeFilters.has(b.dataset.filter)) {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });

        // Filter event items in real-time
        let visibleCount = 0;
        eventItems.forEach(item => {
            const itemCats = (item.dataset.categories || '').split(' ').filter(Boolean);
            let show = false;

            if (activeFilters.has('all')) {
                show = true;
            } else {
                show = itemCats.some(cat => activeFilters.has(cat));
            }

            if (show) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (filterEmptyState) {
            filterEmptyState.classList.toggle('d-none', visibleCount > 0 || eventItems.length === 0);
        }
    });
});
</script>
@endsection
