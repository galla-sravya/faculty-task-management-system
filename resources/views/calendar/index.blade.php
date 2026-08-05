@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Department Task & Meeting Calendar</h1>
        <p class="text-muted small mb-0">Overview of task deadlines, assignments, and scheduled department meetings</p>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
