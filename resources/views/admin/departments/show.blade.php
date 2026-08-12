<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Breadcrumb / Back button -->
        <div class="mb-3">
            <a href="{{ route('admin.departments.index') }}" class="text-decoration-none text-muted small fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Back to Departments
            </a>
        </div>

        <!-- Department Header Banner -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold mb-0" style="color: var(--navy);">{{ $department->name }}</h3>
                            <span class="badge bg-primary fs-6 px-3">{{ $department->code }}</span>
                            @if(($department->status ?? 'active') === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active Department</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive Department</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0">
                            HOD: <strong>{{ $department->hod->name ?? 'Unassigned' }}</strong> | 
                            NBA Coordinator: <strong>{{ $department->nbaCoordinator->name ?? 'Unassigned' }}</strong>
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                            <i class="bi bi-person-plus me-1"></i> Add Faculty Member
                        </button>
                    </div>
                </div>
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

        <!-- Faculty Members Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-people me-2 text-primary"></i>Faculty Members ({{ $facultyMembers->count() }})
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Faculty Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Specialization / Contact</th>
                            <th>Account Status</th>
                            <th>Created Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($facultyMembers as $faculty)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $faculty->profile_photo_url }}" alt="{{ $faculty->name }}" class="rounded-circle object-fit-cover border" style="width:36px; height:36px;">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $faculty->name }}</div>
                                            <div class="small text-muted" style="font-size:0.75rem;">Faculty</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium text-dark">{{ $faculty->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $faculty->designation ?: 'Assistant Professor' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $faculty->specialization ?: 'General' }}</div>
                                    <div class="small text-muted">{{ $faculty->phone ?: '-' }}</div>
                                </td>
                                <td>
                                    @if(($faculty->status ?? 'active') === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $faculty->created_at ? $faculty->created_at->format('M d, Y') : '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="position-relative d-inline-block">
                                        <button class="btn btn-sm btn-light border admin-actions-btn" type="button" data-actions-id="{{ $faculty->id }}">
                                            Actions <i class="bi bi-chevron-down ms-1 small"></i>
                                        </button>
                                        <div class="d-none" id="adminActionsTemplate{{ $faculty->id }}">
                                            <ul class="list-unstyled mb-0 py-1">
                                                <li>
                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#viewFacultyModal{{ $faculty->id }}">
                                                        <i class="bi bi-eye text-info"></i> View Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#editFacultyModal{{ $faculty->id }}">
                                                        <i class="bi bi-pencil text-primary"></i> Edit Faculty
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $faculty->id }}">
                                                        <i class="bi bi-key text-warning"></i> Reset Password
                                                    </button>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.departments.faculty.toggleStatus', [$department, $faculty]) }}" method="POST" class="m-0 p-0">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-dark text-decoration-none border-0 bg-transparent w-100 text-start">
                                                            @if(($faculty->status ?? 'active') === 'active')
                                                                <i class="bi bi-person-x text-secondary"></i> Deactivate
                                                            @else
                                                                <i class="bi bi-person-check text-success"></i> Activate
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form action="{{ route('admin.departments.faculty.destroy', [$department, $faculty]) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to remove this faculty member?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2 px-3 text-decoration-none border-0 bg-transparent w-100 text-start">
                                                            <i class="bi bi-trash"></i> Remove Account
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- View Modal -->
                                    <div class="modal fade text-start" id="viewFacultyModal{{ $faculty->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold" style="color: var(--navy);">Faculty Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="text-center mb-3">
                                                        <img src="{{ $faculty->profile_photo_url }}" alt="{{ $faculty->name }}" class="rounded-circle object-fit-cover border mb-2" style="width:72px; height:72px;">
                                                        <h5 class="fw-bold mb-0">{{ $faculty->name }}</h5>
                                                        <span class="badge bg-secondary rounded-pill mt-1">{{ $faculty->designation ?: 'Faculty Member' }}</span>
                                                    </div>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Email</span>
                                                            <span class="fw-medium text-dark">{{ $faculty->email }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Department</span>
                                                            <span class="fw-medium text-dark">{{ $department->name }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Phone</span>
                                                            <span class="fw-medium text-dark">{{ $faculty->phone ?: '-' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Specialization</span>
                                                            <span class="fw-medium text-dark">{{ $faculty->specialization ?: '-' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Account Status</span>
                                                            <span class="fw-medium">{{ ucfirst($faculty->status ?? 'active') }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Joined Date</span>
                                                            <span class="fw-medium">{{ $faculty->created_at ? $faculty->created_at->format('M d, Y') : '-' }}</span>
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
                                    <div class="modal fade text-start" id="editFacultyModal{{ $faculty->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.departments.faculty.update', [$department, $faculty]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Edit Faculty Member</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Full Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $faculty->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control" value="{{ $faculty->email }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Department</label>
                                                            <select name="department_id" class="form-select" required>
                                                                @foreach($allDepartments as $deptOption)
                                                                    <option value="{{ $deptOption->id }}" {{ $faculty->department_id == $deptOption->id ? 'selected' : '' }}>
                                                                        {{ $deptOption->name }} ({{ $deptOption->code }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Designation</label>
                                                            <input type="text" name="designation" class="form-control" value="{{ $faculty->designation }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Phone Number</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $faculty->phone }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Specialization</label>
                                                            <input type="text" name="specialization" class="form-control" value="{{ $faculty->specialization }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Account Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="active" {{ ($faculty->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ ($faculty->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                    <div class="modal fade text-start" id="resetPasswordModal{{ $faculty->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.departments.faculty.resetPassword', [$department, $faculty]) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Reset Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted">Set a new password for <strong>{{ $faculty->name }}</strong>.</p>
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
                                <td colspan="7" class="text-center py-4 text-muted">No faculty members found in {{ $department->name }}. Click "Add Faculty Member" to add one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Faculty Modal -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.departments.faculty.store', $department) }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Add Faculty Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border small text-muted mb-3">
                            Adding faculty to department: <strong>{{ $department->name }} ({{ $department->code }})</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Dr. Sarah Connor" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="sarah.cs@psgitech.ac.in" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder="Assistant Professor (Sr. Gr.)" value="Assistant Professor">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="9876543210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Specialization</label>
                                <input type="text" name="specialization" class="form-control" placeholder="AI / Machine Learning">
                            </div>
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
                        <button type="submit" class="btn btn-primary rounded-pill">Create Faculty Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
