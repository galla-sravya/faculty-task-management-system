<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--navy);">HOD Accounts Management</h3>
                <p class="text-muted small mb-0">Manage Head of Department accounts across all institution departments.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addHodModal">
                    <i class="bi bi-person-plus me-1"></i> Add HOD Account
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
                    <i class="bi bi-person-badge me-2 text-primary"></i>HOD Accounts List ({{ $hods->count() }})
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Account Status</th>
                            <th>Created Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hods as $hod)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle" style="width:38px; height:38px;">
                                            {{ strtoupper(substr($hod->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $hod->name }}</div>
                                            <div class="small text-muted" style="font-size:0.75rem;">HOD Role</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium text-dark">{{ $hod->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $hod->department->name ?? 'Unassigned' }} ({{ $hod->department->code ?? '-' }})
                                    </span>
                                </td>
                                <td>
                                    @if(($hod->status ?? 'active') === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $hod->created_at ? $hod->created_at->format('M d, Y') : '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="position-relative d-inline-block">
                                        <button class="btn btn-sm btn-light border hod-actions-btn" type="button" data-hod-id="{{ $hod->id }}">
                                            Actions <i class="bi bi-chevron-down ms-1 small"></i>
                                        </button>
                                        <div class="d-none" id="hodActionsMenuTemplate{{ $hod->id }}">
                                            <ul class="list-unstyled mb-0 py-1">
                                                <li>
                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#viewHodModal{{ $hod->id }}">
                                                        <i class="bi bi-eye text-info"></i> View Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#editHodModal{{ $hod->id }}">
                                                        <i class="bi bi-pencil text-primary"></i> Edit HOD
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $hod->id }}">
                                                        <i class="bi bi-key text-warning"></i> Reset Password
                                                    </button>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.hods.toggleStatus', $hod) }}" method="POST" class="m-0 p-0">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start">
                                                            @if(($hod->status ?? 'active') === 'active')
                                                                <i class="bi bi-person-x text-secondary"></i> Deactivate
                                                            @else
                                                                <i class="bi bi-person-check text-success"></i> Activate
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form action="{{ route('admin.hods.destroy', $hod) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this HOD account? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2 px-3 text-decoration-none border-0 bg-transparent w-100 text-start">
                                                            <i class="bi bi-trash"></i> Delete Account
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- View Details Modal -->
                                    <div class="modal fade text-start" id="viewHodModal{{ $hod->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold" style="color: var(--navy);">HOD Account Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="text-center mb-3">
                                                        <div class="avatar-lg rounded-circle bg-primary bg-opacity-10 text-primary fw-bold mx-auto d-flex align-items-center justify-content-center border" style="width:64px; height:64px; font-size: 1.5rem;">
                                                            {{ strtoupper(substr($hod->name, 0, 1)) }}
                                                        </div>
                                                        <h5 class="fw-bold mt-2 mb-0">{{ $hod->name }}</h5>
                                                        <span class="badge bg-primary rounded-pill mt-1">Head of Department</span>
                                                    </div>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Email</span>
                                                            <span class="fw-medium text-dark">{{ $hod->email }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Department</span>
                                                            <span class="fw-medium text-dark">{{ $hod->department->name ?? 'Unassigned' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Account Status</span>
                                                            <span class="fw-medium">{{ ucfirst($hod->status ?? 'active') }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Created Date</span>
                                                            <span class="fw-medium">{{ $hod->created_at ? $hod->created_at->format('M d, Y') : '-' }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit HOD Modal -->
                                    <div class="modal fade text-start" id="editHodModal{{ $hod->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.hods.update', $hod) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Edit HOD Account</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Full Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $hod->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control" value="{{ $hod->email }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Department</label>
                                                            <select name="department_id" class="form-select" required>
                                                                @foreach($departments as $dept)
                                                                    <option value="{{ $dept->id }}" {{ $hod->department_id == $dept->id ? 'selected' : '' }}>
                                                                        {{ $dept->name }} ({{ $dept->code }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Account Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="active" {{ ($hod->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ ($hod->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">New Password (optional)</label>
                                                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reset Password Modal -->
                                    <div class="modal fade text-start" id="resetPasswordModal{{ $hod->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.hods.resetPassword', $hod) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Reset Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted">Set a new password for <strong>{{ $hod->name }}</strong> ({{ $hod->email }}).</p>
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
                                <td colspan="6" class="text-center py-4 text-muted">No HOD accounts found. Click "Add HOD Account" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add HOD Modal -->
    <div class="modal fade" id="addHodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.hods.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Add New HOD Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Dr. John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="hod.cse@psgitech.ac.in" required>
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
                        <button type="submit" class="btn btn-primary rounded-pill">Create HOD Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
