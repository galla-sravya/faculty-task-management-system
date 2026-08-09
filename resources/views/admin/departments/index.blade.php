<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--navy);">Department Management</h3>
                <p class="text-muted small mb-0">Manage institution departments, assign HODs, and view department faculty.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Department
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

        <!-- Departments List Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-building me-2 text-primary"></i>Departments List ({{ $departments->count() }})
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Department Name</th>
                            <th>Code</th>
                            <th>HOD</th>
                            <th>NBA Coordinator</th>
                            <th>Faculty Count</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $dept->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $dept->code }}</span>
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
                                    <span class="badge bg-secondary rounded-pill px-3 py-1">
                                        <i class="bi bi-people me-1"></i>{{ $dept->faculty_count }} Faculty
                                    </span>
                                </td>
                                <td>
                                    @if(($dept->status ?? 'active') === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <a href="{{ route('admin.departments.show', $dept) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye me-1"></i> View Faculty
                                        </a>
                                        <button class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editDeptModal{{ $dept->id }}">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.departments.toggleStatus', $dept) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border">
                                                @if(($dept->status ?? 'active') === 'active')
                                                    <span class="text-secondary">Deactivate</span>
                                                @else
                                                    <span class="text-success">Activate</span>
                                                @endif
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" {{ $dept->faculty_count > 0 ? 'disabled title="Cannot delete department with faculty"' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Department Modal -->
                                    <div class="modal fade text-start" id="editDeptModal{{ $dept->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.departments.update', $dept) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Edit Department</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Department Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $dept->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Department Code</label>
                                                            <input type="text" name="code" class="form-control text-uppercase" value="{{ $dept->code }}" required style="letter-spacing:1px;">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Head of Department (HOD)</label>
                                                            <select name="hod_id" class="form-select">
                                                                <option value="">-- Unassigned --</option>
                                                                @foreach($hods as $h)
                                                                    <option value="{{ $h->id }}" {{ $dept->hod_id == $h->id ? 'selected' : '' }}>{{ $h->name }} ({{ $h->email }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="active" {{ ($dept->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ ($dept->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            </select>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No departments found. Click "Add Department" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Add Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Department Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Computer Science and Engineering" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Department Code</label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="CSE" required style="letter-spacing:1px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign HOD (Optional)</label>
                            <select name="hod_id" class="form-select">
                                <option value="">-- Select HOD --</option>
                                @foreach($hods as $h)
                                    <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill">Create Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
