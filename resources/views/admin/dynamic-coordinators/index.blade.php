<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--navy);">Dynamic Coordinator Accounts</h3>
                <p class="text-muted small mb-0">Manage specialized coordinator accounts (Research, Placement, IQAC, Exam, etc.)</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.coordinator-types.index') }}" class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-tags me-1"></i> Manage Role Types
                </a>
                <button type="button" class="btn text-white shadow-sm" style="background-color: #6f42c1;" data-bs-toggle="modal" data-bs-target="#addDynamicModal">
                    <i class="bi bi-person-plus me-1"></i> Add Dynamic Coordinator
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Accounts Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-gear me-2 text-purple" style="color: #6f42c1;"></i>Dynamic Coordinator Accounts List ({{ $coordinators->count() }})
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Coordinator Type</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coordinators as $coord)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center border" style="width:38px; height:38px; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; border-color: rgba(111, 66, 193, 0.2) !important;">
                                            {{ strtoupper(substr($coord->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $coord->name }}</div>
                                            <div class="small text-muted" style="font-size:0.75rem;">Specialized Coordinator</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium text-dark">{{ $coord->email }}</td>
                                <td>
                                    <span class="badge px-2 py-1 rounded-pill small border" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; border-color: rgba(111, 66, 193, 0.2) !important;">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $coord->coordinatorType->name ?? 'Unassigned Type' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $coord->department->name ?? 'Unassigned' }} ({{ $coord->department->code ?? '-' }})
                                    </span>
                                </td>
                                <td>
                                    @if(($coord->status ?? 'active') === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $coord->created_at ? $coord->created_at->format('M d, Y') : '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border">
                                            <li>
                                                <button class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#viewCoordModal{{ $coord->id }}">
                                                    <i class="bi bi-eye text-info"></i> View Details
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#editCoordModal{{ $coord->id }}">
                                                    <i class="bi bi-pencil text-primary"></i> Edit Account
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $coord->id }}">
                                                    <i class="bi bi-key text-warning"></i> Reset Password
                                                </button>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.dynamic-coordinators.toggleStatus', $coord) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                                        @if(($coord->status ?? 'active') === 'active')
                                                            <i class="bi bi-person-x text-secondary"></i> Deactivate
                                                        @else
                                                            <i class="bi bi-person-check text-success"></i> Activate
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.dynamic-coordinators.destroy', $coord) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coordinator account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                                        <i class="bi bi-trash"></i> Delete Account
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- View Details Modal -->
                                    <div class="modal fade text-start" id="viewCoordModal{{ $coord->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold" style="color: var(--navy);">Coordinator Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="text-center mb-3">
                                                        <div class="avatar-lg rounded-circle mx-auto d-flex align-items-center justify-content-center border" style="width:64px; height:64px; font-size: 1.5rem; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                                            {{ strtoupper(substr($coord->name, 0, 1)) }}
                                                        </div>
                                                        <h5 class="fw-bold mt-2 mb-0">{{ $coord->name }}</h5>
                                                        <span class="badge rounded-pill mt-1" style="background-color: #6f42c1; color: white;">
                                                            {{ $coord->coordinatorType->name ?? 'Dynamic Coordinator' }}
                                                        </span>
                                                    </div>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Email</span>
                                                            <span class="fw-medium text-dark">{{ $coord->email }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Coordinator Type</span>
                                                            <span class="fw-medium text-dark">{{ $coord->coordinatorType->name ?? 'N/A' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Department</span>
                                                            <span class="fw-medium text-dark">{{ $coord->department->name ?? 'Unassigned' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Status</span>
                                                            <span class="fw-medium">{{ ucfirst($coord->status ?? 'active') }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Created Date</span>
                                                            <span class="fw-medium">{{ $coord->created_at ? $coord->created_at->format('M d, Y') : '-' }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade text-start" id="editCoordModal{{ $coord->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.dynamic-coordinators.update', $coord) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Edit Coordinator Account</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Full Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $coord->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control" value="{{ $coord->email }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Coordinator Type</label>
                                                            <select name="coordinator_type_id" class="form-select" required>
                                                                @foreach($coordinatorTypes as $type)
                                                                    <option value="{{ $type->id }}" {{ $coord->coordinator_type_id == $type->id ? 'selected' : '' }}>
                                                                        {{ $type->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Department</label>
                                                            <select name="department_id" class="form-select" required>
                                                                @foreach($departments as $dept)
                                                                    <option value="{{ $dept->id }}" {{ $coord->department_id == $dept->id ? 'selected' : '' }}>
                                                                        {{ $dept->name }} ({{ $dept->code }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Account Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="active" {{ ($coord->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ ($coord->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">New Password (optional)</label>
                                                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn text-white rounded-pill" style="background-color: #6f42c1;">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reset Password Modal -->
                                    <div class="modal fade text-start" id="resetPasswordModal{{ $coord->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.dynamic-coordinators.resetPassword', $coord) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Reset Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted">Set a new password for <strong>{{ $coord->name }}</strong>.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">New Password</label>
                                                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Enter new password">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning rounded-pill text-dark fw-bold">Update Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No dynamic coordinators created yet. Click "Add Dynamic Coordinator" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addDynamicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.dynamic-coordinators.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Add Dynamic Coordinator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Dr. Alice Smith" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="research.cse@psgitech.ac.in" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Coordinator Type</label>
                            <select name="coordinator_type_id" class="form-select" required>
                                <option value="">-- Select Coordinator Type --</option>
                                @foreach($coordinatorTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @if($coordinatorTypes->isEmpty())
                                <div class="form-text text-danger">No active coordinator types found. Please create one in <a href="{{ route('admin.coordinator-types.index') }}">Role Types</a> first.</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Initial Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Account Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn text-white rounded-pill" style="background-color: #6f42c1;" {{ $coordinatorTypes->isEmpty() ? 'disabled' : '' }}>Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
