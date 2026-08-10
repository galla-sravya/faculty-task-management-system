@extends('layouts.app')

@section('content')
<!-- Include FullCalendar Core and styling -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

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

        <!-- FullCalendar Container -->
        <div id="calendar" class="mt-4"></div>

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
    
    /* FullCalendar styling overrides to match theme */
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e9ecef;
    }
    .fc .fc-toolbar-title {
        color: var(--navy);
        font-weight: 700;
        font-size: 1.5rem;
    }
    .fc .fc-button-primary {
        background-color: var(--navy);
        border-color: var(--navy);
    }
    .fc .fc-button-primary:hover {
        background-color: #0d1d42;
        border-color: #0d1d42;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, .fc .fc-button-primary:not(:disabled):active {
        background-color: #0a1632;
        border-color: #0a1632;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        border: none;
        border-radius: 4px;
        margin-bottom: 2px;
        font-size: 0.8rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Get Events from PHP
    const allEvents = @json($events->values());
    
    // 2. Compute Filter Counts
    const counts = { all: allEvents.length };
    allEvents.forEach(event => {
        const cats = event.categories || [];
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

    // 3. Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    
    // Active filters tracking
    let activeFilters = new Set(['all']);
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            // Filter events based on active filters before giving them to calendar
            let filteredEvents = allEvents.filter(event => {
                if (activeFilters.has('all')) return true;
                const cats = event.categories || [];
                return cats.some(cat => activeFilters.has(cat));
            });
            successCallback(filteredEvents);
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // don't let the browser navigate
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        }
    });
    
    calendar.render();

    // 4. Handle Filter Clicks
    const filterContainer = document.getElementById('calendar-filter-chips');
    
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

        // Tell calendar to refetch/re-filter events
        calendar.refetchEvents();
    });
});
</script>
@endsection
