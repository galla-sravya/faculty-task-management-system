/**
 * Inline Header Filter Engine (Modern & Clean)
 * Clickable column headings open a sleek dropdown directly beneath the header.
 * Instant filtering, multi-column combination, click-outside auto-close, and sessionStorage filter persistence.
 * Floating dropdown positioning ensures standard table responsiveness is NEVER broken.
 */

document.addEventListener('DOMContentLoaded', function () {
    initTaskTableFilters();
});

function initTaskTableFilters() {
    const wrappers = document.querySelectorAll('.task-table-wrapper');
    wrappers.forEach(wrapper => {
        setupWrapperFiltering(wrapper);
    });

    // Close all open header dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.th-inline-dropdown') && !e.target.closest('th[data-filter-col]')) {
            closeAllFilterDropdowns();
        }
    });

    // Close dropdowns on window scroll or resize to maintain precise alignment
    window.addEventListener('scroll', function () {
        closeAllFilterDropdowns();
    }, { passive: true });

    window.addEventListener('resize', function () {
        closeAllFilterDropdowns();
    });
}

function closeAllFilterDropdowns() {
    document.querySelectorAll('.th-inline-dropdown.show').forEach(dd => {
        dd.classList.remove('show');
    });
}

function setupWrapperFiltering(wrapper) {
    const table = wrapper.querySelector('table');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const filterChipsContainer = wrapper.querySelector('.filter-chips-container');

    // Storage key for state persistence across page navigation
    const storageKey = 'taskFilters_' + window.location.pathname;

    const state = loadState(storageKey) || {
        search: '',
        filters: {
            title: '',
            priority: 'all',
            status: 'all',
            assigned_date: 'all',
            deadline: 'all',
            faculty: 'all',
            category: 'all',
            progress: 'all'
        },
        sort: { col: null, dir: 'asc' }
    };

    // Ensure sort state exists for legacy cached state
    if (!state.sort) {
        state.sort = { col: null, dir: 'asc' };
    }

    // Store original row elements
    const originalRows = Array.from(tbody.querySelectorAll('tr[data-task-row]'));

    // Dynamically collect unique faculty names and categories present in the table
    const dynamicFaculty = collectUniqueValues(originalRows, 'data-faculty');
    const dynamicCategories = collectUniqueValues(originalRows, 'data-category');

    // Setup Inline Header Filter Controls
    setupInlineHeaders(table, state, dynamicFaculty, dynamicCategories, applyFilters);

    function applyFilters() {
        let completedCount = 0;
        let inProgressCount = 0;
        let overdueCount = 0;
        let totalFiltered = 0;

        const filteredRows = [];

        originalRows.forEach(row => {
            const isMatch = checkRowMatch(row, state);
            if (isMatch) {
                filteredRows.push(row);
                totalFiltered++;

                const status = (row.getAttribute('data-status') || '').toLowerCase();
                const isOverdue = row.getAttribute('data-deadline-status') === 'overdue';

                if (status === 'completed') completedCount++;
                else if (['in_progress', 'working_on_task', 'collecting_resources', 'documents_uploaded', 'checklist_completed'].includes(status)) inProgressCount++;
                if (isOverdue && status !== 'completed') overdueCount++;
            }
        });

        if (state.sort && state.sort.col) {
            sortRows(filteredRows, state.sort);
        }

        // Render filtered rows back into tbody
        tbody.innerHTML = '';
        if (filteredRows.length > 0) {
            filteredRows.forEach(row => {
                row.style.display = '';
                tbody.appendChild(row);
            });
        } else {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'no-matching-tasks-row';
            emptyRow.innerHTML = `<td colspan="12" class="text-center py-5 text-muted">
                <i class="bi bi-funnel fs-2 d-block mb-2 text-secondary opacity-50"></i>
                <span class="fw-semibold">No tasks match your selected filter criteria.</span><br>
                <button type="button" class="btn btn-sm btn-link text-decoration-none mt-2 clear-all-btn-action">Clear Filters</button>
            </td>`;
            tbody.appendChild(emptyRow);
            const clearBtn = emptyRow.querySelector('.clear-all-btn-action');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    resetAllFilters(state);
                    syncDropdownValues(table, state);
                    applyFilters();
                });
            }
        }

        // Render Active Filter Chips
        renderFilterChips(filterChipsContainer, state, applyFilters, table);

        // Highlight header labels with active filters
        updateHeaderActiveStates(table, state);

        updateSortIcons(table, state);

        // Persist filter state in sessionStorage
        saveState(storageKey, state);

        // Dispatch Custom Event for Dashboard Charts & Stat Cards Sync
        const event = new CustomEvent('taskTableFiltered', {
            detail: {
                total: totalFiltered,
                completed: completedCount,
                in_progress: inProgressCount,
                overdue: overdueCount,
                completion_rate: totalFiltered > 0 ? Math.round((completedCount / totalFiltered) * 100) : 0
            }
        });
        wrapper.dispatchEvent(event);
        document.dispatchEvent(event);
    }

    // Initial Filter Execution
    applyFilters();
}

function collectUniqueValues(rows, attr) {
    const values = new Set();
    rows.forEach(r => {
        const val = r.getAttribute(attr);
        if (val) {
            val.split(',').forEach(v => {
                const trimmed = v.trim();
                if (trimmed) values.add(trimmed);
            });
        }
    });
    return Array.from(values).sort();
}

function setupInlineHeaders(table, state, dynamicFaculty, dynamicCategories, applyCallback) {
    const headers = table.querySelectorAll('th[data-filter-col]');
    headers.forEach(th => {
        const colKey = th.getAttribute('data-filter-col');
        const titleText = th.textContent.trim();

        // Style th as interactive header trigger
        th.style.position = 'relative';
        th.style.cursor = 'pointer';
        th.classList.add('user-select-none', 'th-filter-header');

        // Render column heading with inline dropdown template
        th.innerHTML = `
            <span class="th-filter-label" style="display:inline-flex; align-items:center;">${titleText}</span>
            <div class="th-inline-dropdown" onclick="event.stopPropagation();">
                ${renderDropdownContent(colKey, state, dynamicFaculty, dynamicCategories)}
            </div>
        `;

        const label = th.querySelector('.th-filter-label');
        if (label) {
            label.addEventListener('dblclick', function (e) {
                e.stopPropagation();
                if (state.sort && state.sort.col === colKey) {
                    state.sort.dir = state.sort.dir === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sort = { col: colKey, dir: 'asc' };
                }
                applyCallback();
            });
            // Prevent text selection on double click
            label.addEventListener('mousedown', function (e) {
                if (e.detail > 1) {
                    e.preventDefault();
                }
            });
        }

        // Toggle dropdown on header click with fixed positioning
        th.addEventListener('click', function (e) {
            if (e.target.closest('.th-inline-dropdown')) return;

            const dropdown = th.querySelector('.th-inline-dropdown');
            const isAlreadyOpen = dropdown.classList.contains('show');

            closeAllFilterDropdowns();

            if (!isAlreadyOpen) {
                const rect = th.getBoundingClientRect();
                dropdown.style.position = 'fixed';
                dropdown.style.top = (rect.bottom + 4) + 'px';

                // Prevent overflow beyond right viewport edge
                const maxLeft = Math.max(10, Math.min(rect.left, window.innerWidth - 250));
                dropdown.style.left = maxLeft + 'px';

                dropdown.classList.add('show');
            }
        });

        // Attach event listeners to inputs inside dropdown
        attachFilterListeners(th, colKey, state, applyCallback);
    });
}

function renderDropdownContent(colKey, state, dynamicFaculty, dynamicCategories) {
    if (colKey === 'title') {
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Task</div>
            <input type="text" class="form-control form-control-sm col-filter-control" placeholder="Search title..." value="${state.filters.title || ''}">
        </div>`;
    }

    if (colKey === 'faculty') {
        let options = '<option value="all"' + (state.filters.faculty === 'all' ? ' selected' : '') + '>All Faculty</option>';
        dynamicFaculty.forEach(name => {
            const sel = state.filters.faculty === name ? ' selected' : '';
            options += `<option value="${name}"${sel}>${name}</option>`;
        });
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Faculty</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    if (colKey === 'status') {
        const statuses = [
            { value: 'all', label: 'All' },
            { value: 'not_started', label: 'Not Started' },
            { value: 'collecting_resources', label: 'Collecting Resources' },
            { value: 'in_progress', label: 'Working on Task' },
            { value: 'documents_uploaded', label: 'Documents Uploaded' },
            { value: 'checklist_completed', label: 'Checklist Completed' },
            { value: 'pending_review', label: 'Submitted for Review' },
            { value: 'approved', label: 'Approved' },
            { value: 'completed', label: 'Completed' },
            { value: 'overdue', label: 'Overdue' }
        ];
        let options = statuses.map(s =>
            `<option value="${s.value}"${state.filters.status === s.value ? ' selected' : ''}>${s.label}</option>`
        ).join('');
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Status</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    if (colKey === 'priority') {
        const priorities = [
            { value: 'all', label: 'All' },
            { value: 'low', label: 'Low' },
            { value: 'medium', label: 'Medium' },
            { value: 'high', label: 'High' },
            { value: 'urgent', label: 'Urgent' }
        ];
        let options = priorities.map(p =>
            `<option value="${p.value}"${state.filters.priority === p.value ? ' selected' : ''}>${p.label}</option>`
        ).join('');
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Priority</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    if (colKey === 'deadline') {
        const deadlines = [
            { value: 'all', label: 'All' },
            { value: 'overdue', label: 'Overdue' },
            { value: 'today', label: 'Due Today' },
            { value: 'tomorrow', label: 'Due Tomorrow' },
            { value: 'next_7_days', label: 'Next 7 Days' },
            { value: 'this_month', label: 'This Month' }
        ];
        let options = deadlines.map(d =>
            `<option value="${d.value}"${state.filters.deadline === d.value ? ' selected' : ''}>${d.label}</option>`
        ).join('');
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Deadline</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    if (colKey === 'assigned_date') {
        const dates = [
            { value: 'all', label: 'All' },
            { value: 'today', label: 'Today' },
            { value: 'yesterday', label: 'Yesterday' },
            { value: 'this_week', label: 'This Week' },
            { value: 'this_month', label: 'This Month' }
        ];
        let options = dates.map(d =>
            `<option value="${d.value}"${state.filters.assigned_date === d.value ? ' selected' : ''}>${d.label}</option>`
        ).join('');
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Assigned Date</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    if (colKey === 'category') {
        let options = '<option value="all"' + (state.filters.category === 'all' ? ' selected' : '') + '>All Categories</option>';
        dynamicCategories.forEach(cat => {
            const sel = state.filters.category === cat.toLowerCase() ? ' selected' : '';
            options += `<option value="${cat.toLowerCase()}"${sel}>${cat}</option>`;
        });
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Category</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    if (colKey === 'progress') {
        const ranges = [
            { value: 'all', label: 'All' },
            { value: '0-25', label: '0–25%' },
            { value: '26-50', label: '26–50%' },
            { value: '51-75', label: '51–75%' },
            { value: '76-99', label: '76–99%' },
            { value: '100', label: '100%' }
        ];
        let options = ranges.map(r =>
            `<option value="${r.value}"${state.filters.progress === r.value ? ' selected' : ''}>${r.label}</option>`
        ).join('');
        return `<div class="th-dropdown-inner">
            <div class="th-dropdown-title">Filter Progress</div>
            <select class="form-select form-select-sm col-filter-control">${options}</select>
        </div>`;
    }

    return '';
}

function attachFilterListeners(th, colKey, state, applyCallback) {
    const control = th.querySelector('.col-filter-control');
    if (!control) return;

    if (control.tagName === 'SELECT') {
        control.addEventListener('change', function () {
            state.filters[colKey] = this.value;
            applyCallback();
            closeAllFilterDropdowns();
        });
    } else if (control.tagName === 'INPUT') {
        let debounceTimer;
        control.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                state.filters[colKey] = this.value.trim();
                applyCallback();
            }, 250);
        });
        control.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                state.filters[colKey] = this.value.trim();
                applyCallback();
                closeAllFilterDropdowns();
            }
        });
    }
}

function updateHeaderActiveStates(table, state) {
    const headers = table.querySelectorAll('th[data-filter-col]');
    headers.forEach(th => {
        const colKey = th.getAttribute('data-filter-col');
        const filterVal = state.filters[colKey];
        const isActive = filterVal && filterVal !== 'all' && filterVal !== '';

        if (isActive) {
            th.classList.add('th-filter-active');
        } else {
            th.classList.remove('th-filter-active');
        }
    });
}

function checkRowMatch(row, state) {
    const title = (row.getAttribute('data-title') || row.innerText || '').toLowerCase();
    const priority = (row.getAttribute('data-priority') || '').toLowerCase();
    const status = (row.getAttribute('data-status') || '').toLowerCase();
    const assignedDate = row.getAttribute('data-assigned-date') || '';
    const deadlineDate = row.getAttribute('data-deadline-date') || '';
    const deadlineStatus = row.getAttribute('data-deadline-status') || '';
    const faculty = (row.getAttribute('data-faculty') || '').toLowerCase();
    const category = (row.getAttribute('data-category') || '').toLowerCase();
    const progress = parseInt(row.getAttribute('data-progress') || '0', 10);

    // Title Filter
    if (state.filters.title) {
        if (!title.includes(state.filters.title.toLowerCase())) return false;
    }

    // Priority Filter
    if (state.filters.priority !== 'all') {
        if (priority !== state.filters.priority.toLowerCase()) return false;
    }

    // Status Filter
    if (state.filters.status !== 'all') {
        const reqStatus = state.filters.status.toLowerCase();
        if (reqStatus === 'not_started') {
            if (status !== 'pending' && status !== 'not_started') return false;
        } else if (reqStatus === 'collecting_resources') {
            if (status !== 'collecting_resources') return false;
        } else if (reqStatus === 'in_progress') {
            if (status !== 'in_progress' && status !== 'working_on_task') return false;
        } else if (reqStatus === 'documents_uploaded') {
            if (status !== 'documents_uploaded') return false;
        } else if (reqStatus === 'checklist_completed') {
            if (status !== 'checklist_completed') return false;
        } else if (reqStatus === 'pending_review') {
            if (status !== 'pending_review' && status !== 'submitted_for_review') return false;
        } else if (reqStatus === 'approved') {
            if (status !== 'approved') return false;
        } else if (reqStatus === 'completed') {
            if (status !== 'completed') return false;
        } else if (reqStatus === 'overdue') {
            if (deadlineStatus !== 'overdue' && status !== 'overdue') return false;
        }
    }

    // Faculty Filter
    if (state.filters.faculty !== 'all') {
        if (!faculty.includes(state.filters.faculty.toLowerCase())) return false;
    }

    // Category Filter
    if (state.filters.category !== 'all') {
        if (category !== state.filters.category.toLowerCase()) return false;
    }

    // Progress Filter
    if (state.filters.progress !== 'all') {
        const pr = state.filters.progress;
        if (pr === '0-25' && (progress < 0 || progress > 25)) return false;
        if (pr === '26-50' && (progress < 26 || progress > 50)) return false;
        if (pr === '51-75' && (progress < 51 || progress > 75)) return false;
        if (pr === '76-99' && (progress < 76 || progress > 99)) return false;
        if (pr === '100' && progress !== 100) return false;
    }

    // Deadline Filter
    if (state.filters.deadline !== 'all') {
        const df = state.filters.deadline;
        if (df === 'overdue' && deadlineStatus !== 'overdue') return false;
        if (df === 'today' && deadlineStatus !== 'today') return false;
        if (df === 'tomorrow' && deadlineStatus !== 'tomorrow') return false;
        if (df === 'next_7_days' && deadlineStatus !== 'today' && deadlineStatus !== 'tomorrow' && deadlineStatus !== 'upcoming_7') return false;
        if (df === 'this_month') {
            if (!deadlineDate) return false;
            const d = new Date(deadlineDate);
            const now = new Date();
            if (d.getMonth() !== now.getMonth() || d.getFullYear() !== now.getFullYear()) return false;
        }
    }

    // Assigned Date Filter
    if (state.filters.assigned_date !== 'all') {
        const af = state.filters.assigned_date;
        if (!assignedDate) return false;
        const d = new Date(assignedDate);
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        if (af === 'today') {
            if (d.toISOString().split('T')[0] !== now.toISOString().split('T')[0]) return false;
        } else if (af === 'yesterday') {
            const yest = new Date(now);
            yest.setDate(now.getDate() - 1);
            if (d.toISOString().split('T')[0] !== yest.toISOString().split('T')[0]) return false;
        } else if (af === 'this_week') {
            const dayOfWeek = now.getDay();
            const firstDay = new Date(now);
            firstDay.setDate(now.getDate() - dayOfWeek);
            const lastDay = new Date(firstDay);
            lastDay.setDate(firstDay.getDate() + 6);
            if (d < firstDay || d > lastDay) return false;
        } else if (af === 'this_month') {
            if (d.getMonth() !== new Date().getMonth() || d.getFullYear() !== new Date().getFullYear()) return false;
        }
    }

    return true;
}

function renderFilterChips(container, state, applyCallback, table) {
    if (!container) return;

    const activeChips = [];

    if (state.filters.title) {
        activeChips.push({ key: 'title', label: `Title: "${state.filters.title}"`, clear: () => { state.filters.title = ''; } });
    }
    if (state.filters.priority !== 'all') {
        activeChips.push({ key: 'priority', label: `Priority: ${capitalize(state.filters.priority)}`, clear: () => { state.filters.priority = 'all'; } });
    }
    if (state.filters.status !== 'all') {
        activeChips.push({ key: 'status', label: `Status: ${formatStatusLabel(state.filters.status)}`, clear: () => { state.filters.status = 'all'; } });
    }
    if (state.filters.assigned_date !== 'all') {
        activeChips.push({ key: 'assigned_date', label: `Assigned: ${formatDateFilterLabel(state.filters.assigned_date)}`, clear: () => { state.filters.assigned_date = 'all'; } });
    }
    if (state.filters.deadline !== 'all') {
        activeChips.push({ key: 'deadline', label: `Deadline: ${formatDeadlineFilterLabel(state.filters.deadline)}`, clear: () => { state.filters.deadline = 'all'; } });
    }
    if (state.filters.faculty !== 'all' && state.filters.faculty !== '') {
        activeChips.push({ key: 'faculty', label: `Faculty: ${state.filters.faculty}`, clear: () => { state.filters.faculty = 'all'; } });
    }
    if (state.filters.category !== 'all' && state.filters.category !== '') {
        activeChips.push({ key: 'category', label: `Category: ${capitalize(state.filters.category)}`, clear: () => { state.filters.category = 'all'; } });
    }
    if (state.filters.progress !== 'all') {
        activeChips.push({ key: 'progress', label: `Progress: ${state.filters.progress}%`, clear: () => { state.filters.progress = 'all'; } });
    }

    if (activeChips.length === 0) {
        container.innerHTML = '';
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';
    container.className = 'filter-chips-container d-flex flex-wrap align-items-center gap-2 mb-3 px-3 py-2 rounded-3';
    container.style.background = '#f0f4f8';
    container.style.border = '1px solid #dce3eb';

    let html = `<span class="small fw-semibold text-muted me-1"><i class="bi bi-funnel me-1"></i>Active Filters:</span>`;

    activeChips.forEach((chip, idx) => {
        html += `
            <span class="badge bg-white text-dark border shadow-sm d-inline-flex align-items-center gap-1 py-1 px-2 fw-medium" style="font-size: 0.78rem; border-radius: 20px;">
                <span>${chip.label}</span>
                <button type="button" class="btn-close btn-close-xs ms-1 remove-chip-btn" data-idx="${idx}" style="font-size: 0.55rem;" aria-label="Remove filter"></button>
            </span>
        `;
    });

    html += `
        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none fw-semibold p-0 ms-auto clear-all-filters-btn" style="font-size: 0.78rem;">
            <i class="bi bi-x-circle me-1"></i>Clear All
        </button>
    `;

    container.innerHTML = html;

    // Attach chip remove handlers
    container.querySelectorAll('.remove-chip-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-idx'), 10);
            if (activeChips[idx]) {
                activeChips[idx].clear();
                syncDropdownValues(table, state);
                applyCallback();
            }
        });
    });

    // Attach clear all handler
    const clearAllBtn = container.querySelector('.clear-all-filters-btn');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            resetAllFilters(state);
            syncDropdownValues(table, state);
            applyCallback();
        });
    }
}

function syncDropdownValues(table, state) {
    if (!table) return;
    const headers = table.querySelectorAll('th[data-filter-col]');
    headers.forEach(th => {
        const colKey = th.getAttribute('data-filter-col');
        const control = th.querySelector('.col-filter-control');
        if (!control) return;

        const val = state.filters[colKey];
        if (control.tagName === 'SELECT') {
            control.value = val || 'all';
        } else if (control.tagName === 'INPUT') {
            control.value = val || '';
        }
    });
}

function resetAllFilters(state) {
    state.search = '';
    state.filters.title = '';
    state.filters.priority = 'all';
    state.filters.status = 'all';
    state.filters.assigned_date = 'all';
    state.filters.deadline = 'all';
    state.filters.faculty = 'all';
    state.filters.category = 'all';
    state.filters.progress = 'all';
}

function saveState(key, state) {
    try {
        sessionStorage.setItem(key, JSON.stringify(state));
    } catch (e) { /* silent */ }
}

function loadState(key) {
    try {
        const raw = sessionStorage.getItem(key);
        if (raw) return JSON.parse(raw);
    } catch (e) { /* silent */ }
    return null;
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
}

function formatStatusLabel(status) {
    const map = {
        not_started: 'Not Started',
        collecting_resources: 'Collecting Resources',
        in_progress: 'Working on Task',
        documents_uploaded: 'Documents Uploaded',
        checklist_completed: 'Checklist Completed',
        pending_review: 'Submitted for Review',
        approved: 'Approved',
        completed: 'Completed',
        overdue: 'Overdue'
    };
    return map[status] || capitalize(status);
}

function formatDateFilterLabel(af) {
    const map = {
        today: 'Today',
        yesterday: 'Yesterday',
        this_week: 'This Week',
        this_month: 'This Month'
    };
    return map[af] || af;
}

function formatDeadlineFilterLabel(df) {
    const map = {
        overdue: 'Overdue',
        today: 'Due Today',
        tomorrow: 'Due Tomorrow',
        next_7_days: 'Next 7 Days',
        this_month: 'This Month'
    };
    return map[df] || df;
}

function sortRows(rows, sortState) {
    const { col, dir } = sortState;
    const modifier = dir === 'asc' ? 1 : -1;

    rows.sort((a, b) => {
        if (col === 'priority') {
            const map = { 'urgent': 4, 'high': 3, 'medium': 2, 'low': 1 };
            const valA = map[(a.getAttribute('data-priority') || '').toLowerCase()] || 0;
            const valB = map[(b.getAttribute('data-priority') || '').toLowerCase()] || 0;
            return (valB - valA) * modifier;
        } else if (col === 'deadline') {
            const valA = a.getAttribute('data-deadline-date') || '9999-12-31';
            const valB = b.getAttribute('data-deadline-date') || '9999-12-31';
            if (valA < valB) return -1 * modifier;
            if (valA > valB) return 1 * modifier;
            return 0;
        } else if (col === 'status') {
            const map = { 
                'overdue': 4, 
                'in_progress': 3, 'working_on_task': 3, 'collecting_resources': 3, 
                'not_started': 2, 'pending': 2, 'pending_review': 2, 'submitted_for_review': 2, 'documents_uploaded': 2, 'checklist_completed': 2, 
                'completed': 1 
            };
            const valA = map[(a.getAttribute('data-status') || '').toLowerCase()] || 0;
            const valB = map[(b.getAttribute('data-status') || '').toLowerCase()] || 0;
            return (valB - valA) * modifier;
        } else if (col === 'faculty') {
            const valA = (a.getAttribute('data-faculty') || '').toLowerCase();
            const valB = (b.getAttribute('data-faculty') || '').toLowerCase();
            if (valA < valB) return -1 * modifier;
            if (valA > valB) return 1 * modifier;
            return 0;
        } else if (col === 'title') {
            const valA = (a.getAttribute('data-title') || a.innerText || '').toLowerCase();
            const valB = (b.getAttribute('data-title') || b.innerText || '').toLowerCase();
            if (valA < valB) return -1 * modifier;
            if (valA > valB) return 1 * modifier;
            return 0;
        }
        return 0;
    });
}

function updateSortIcons(table, state) {
    const headers = table.querySelectorAll('th[data-filter-col]');
    headers.forEach(th => {
        const colKey = th.getAttribute('data-filter-col');
        const label = th.querySelector('.th-filter-label');
        if (!label) return;
        
        // Remove existing arrow
        const existingArrow = label.querySelector('.sort-arrow');
        if (existingArrow) {
            existingArrow.remove();
        }

        if (state.sort && state.sort.col === colKey) {
            const arrow = document.createElement('span');
            arrow.className = 'sort-arrow ms-1 text-muted';
            arrow.style.fontSize = '0.65rem';
            // Use down arrow for 'asc' (descending urgency/alphabetical) and up arrow for 'desc'
            arrow.textContent = state.sort.dir === 'asc' ? '▼' : '▲';
            label.appendChild(arrow);
        }
    });
}
