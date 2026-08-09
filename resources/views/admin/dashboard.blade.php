<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--navy);">Admin Dashboard</h3>
                <p class="text-muted small mb-0">Centralized Organization & Account Administration Overview</p>
            </div>
            <div>
                <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm">
                    <i class="bi bi-shield-check me-1"></i> System Administrator
                </span>
            </div>
        </div>

        <style>
            .hover-lift {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .hover-lift:hover {
                transform: translateY(-3px);
                box-shadow: 0 .5rem 1rem rgba(0,0,0,.10) !important;
                background-color: #f8f9fa;
            }
        </style>
        <!-- Metric Stat Cards (NO Task metrics) -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.hods.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card border-0 shadow-sm rounded-3 h-100 hover-lift" style="border-left: 4px solid #0d6efd !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">HOD Accounts</div>
                                <div class="h3 fw-bold mb-0 text-dark">{{ $totalHod }}</div>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-badge fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.nba-coordinators.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card border-0 shadow-sm rounded-3 h-100 hover-lift" style="border-left: 4px solid #0dcaf0 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">NBA Coords</div>
                                <div class="h3 fw-bold mb-0 text-dark">{{ $totalNba }}</div>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-workspace fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.dynamic-coordinators.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card border-0 shadow-sm rounded-3 h-100 hover-lift" style="border-left: 4px solid #6f42c1 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">Dynamic Coords</div>
                                <div class="h3 fw-bold mb-0 text-dark">{{ $totalCoordinators }}</div>
                            </div>
                            <div class="rounded-circle bg-purple bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                <i class="bi bi-person-gear fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.departments.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card border-0 shadow-sm rounded-3 h-100 hover-lift" style="border-left: 4px solid #198754 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">Departments</div>
                                <div class="h3 fw-bold mb-0 text-dark">{{ $totalDepartments }}</div>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="row g-4 mb-4">
            <!-- Department Overview -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                        <h6 class="fw-bold mb-0" style="color: var(--navy);">
                            <i class="bi bi-building me-2 text-primary"></i>Department Overview
                        </h6>
                        <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            Manage Departments <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th>Department</th>
                                    <th>Faculty Count</th>
                                    <th>HOD</th>
                                    <th>NBA Coordinator</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $dept)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $dept->name }}</div>
                                            <div class="small text-muted">{{ $dept->code }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill px-2 py-1 fs-7">
                                                <i class="bi bi-people me-1"></i>{{ $dept->faculty_count }} members
                                            </span>
                                        </td>
                                        <td>
                                            @if($dept->hod)
                                                <span class="small fw-medium text-dark"><i class="bi bi-person-check text-success me-1"></i>{{ $dept->hod->name }}</span>
                                            @else
                                                <span class="small text-muted italic">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dept->nbaCoordinator)
                                                <span class="small fw-medium text-dark"><i class="bi bi-person-workspace text-info me-1"></i>{{ $dept->nbaCoordinator->name }}</span>
                                            @else
                                                <span class="small text-muted italic">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(($dept->status ?? 'active') === 'active')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.departments.show', $dept) }}" class="btn btn-sm btn-light border text-primary">
                                                View Faculty <i class="bi bi-chevron-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No departments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Dynamic Coordinators Overview -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                        <h6 class="fw-bold mb-0" style="color: var(--navy);">
                            <i class="bi bi-person-gear me-2 text-purple" style="color: #6f42c1;"></i>Dynamic Coordinators
                        </h6>
                        <a href="{{ route('admin.dynamic-coordinators.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dynamicCoordinators as $coord)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark mb-0">{{ $coord->name }}</div>
                                            <div class="small text-muted" style="font-size:0.75rem;">{{ $coord->email }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple-subtle text-purple border px-2 py-1 rounded-pill small" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; border-color: rgba(111, 66, 193, 0.2) !important;">
                                                {{ $coord->coordinatorType->name ?? 'Coordinator' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-muted">{{ $coord->department->code ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No dynamic coordinators added yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Accounts Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                        <h6 class="fw-bold mb-0" style="color: var(--navy);">
                            <i class="bi bi-clock-history me-2 text-warning"></i>Recent Accounts Created
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAccounts as $account)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold border" style="width:36px; height:36px;">
                                                    {{ strtoupper(substr($account->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $account->name }}</div>
                                                    <div class="small text-muted" style="font-size:0.78rem;">{{ $account->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($account->role === 'hod')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">HOD</span>
                                            @elseif($account->role === 'nba_coordinator')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">NBA Coordinator</span>
                                            @elseif($account->role === 'coordinator')
                                                <span class="badge bg-purple-subtle text-purple border rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                                    {{ $account->coordinatorType->name ?? 'Coordinator' }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Faculty</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-dark fw-medium">{{ $account->department->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if(($account->status ?? 'active') === 'active')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $account->created_at ? $account->created_at->format('M d, Y') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No recent accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
