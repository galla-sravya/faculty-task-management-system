<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--navy);">NBA Coordinator Accounts Management</h3>
                <p class="text-muted small mb-0">Manage NBA Coordinator accounts across all departments.</p>
            </div>
            <div>
                <button type="button" class="btn btn-info text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#addNbaModal">
                    <i class="bi bi-person-plus me-1"></i> Add NBA Coordinator
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

        {{-- Credentials Modal: auto-shown after account creation or password reset --}}
        @if(session('created_credentials'))
            <div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom-0 bg-gradient" style="background: linear-gradient(135deg, #0dcaf0, #0a58ca);">
                            <h5 class="modal-title fw-bold text-white" id="credentialsModalLabel">
                                <i class="bi bi-shield-lock-fill me-2"></i>Account Credentials
                            </h5>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-warning border-0 d-flex align-items-start gap-2 mb-4" style="background: #fff8e1;">
                                <i class="bi bi-exclamation-triangle-fill text-warning mt-1" style="font-size: 1.1rem;"></i>
                                <div>
                                    <strong>Important:</strong> Please copy these credentials now. The password <strong>cannot</strong> be retrieved later as it is stored securely (hashed).
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light fw-medium" value="{{ session('created_credentials.name') }}" readonly id="credName">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('credName')" title="Copy Name">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Email</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light fw-medium" value="{{ session('created_credentials.email') }}" readonly id="credEmail">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('credEmail')" title="Copy Email">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Password</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light fw-bold text-danger" value="{{ session('created_credentials.password') }}" readonly id="credPassword" style="letter-spacing: 1px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('credPassword')" title="Copy Password">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button class="btn btn-outline-info rounded-pill fw-semibold" type="button" onclick="copyAllCredentials()">
                                    <i class="bi bi-clipboard-check me-1"></i> Copy All Credentials
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-info text-white rounded-pill px-4" data-bs-dismiss="modal">
                                <i class="bi bi-check-lg me-1"></i> I've Saved the Credentials
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var credModal = new bootstrap.Modal(document.getElementById('credentialsModal'));
                    credModal.show();
                });

                function copyToClipboard(elementId) {
                    var input = document.getElementById(elementId);
                    navigator.clipboard.writeText(input.value).then(function () {
                        var btn = input.nextElementSibling;
                        var originalHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
                        btn.classList.add('btn-outline-success');
                        btn.classList.remove('btn-outline-secondary');
                        setTimeout(function () {
                            btn.innerHTML = originalHtml;
                            btn.classList.remove('btn-outline-success');
                            btn.classList.add('btn-outline-secondary');
                        }, 1500);
                    });
                }

                function copyAllCredentials() {
                    var name = document.getElementById('credName').value;
                    var email = document.getElementById('credEmail').value;
                    var password = document.getElementById('credPassword').value;
                    var text = 'NBA Coordinator Credentials\n' +
                               '----------------------------\n' +
                               'Name: ' + name + '\n' +
                               'Email: ' + email + '\n' +
                               'Password: ' + password;
                    navigator.clipboard.writeText(text).then(function () {
                        var btn = event.target.closest('button');
                        var originalHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copied!';
                        btn.classList.add('btn-success');
                        btn.classList.remove('btn-outline-info');
                        setTimeout(function () {
                            btn.innerHTML = originalHtml;
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-info');
                        }, 2000);
                    });
                }
            </script>
        @endif

        <!-- Accounts Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-workspace me-2 text-info"></i>NBA Coordinator Accounts List ({{ $nbaCoordinators->count() }})
                </h6>
            </div>
            <div class="table-responsive" style="overflow: visible;">
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
                        @forelse($nbaCoordinators as $nba)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center border border-info-subtle" style="width:38px; height:38px;">
                                            {{ strtoupper(substr($nba->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $nba->name }}</div>
                                            <div class="small text-muted" style="font-size:0.75rem;">NBA Accreditation Coordinator</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium text-dark">{{ $nba->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $nba->department->name ?? 'Unassigned' }} ({{ $nba->department->code ?? '-' }})
                                    </span>
                                </td>
                                <td>
                                    @if(($nba->status ?? 'active') === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">Inactive</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $nba->created_at ? $nba->created_at->format('M d, Y') : '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3">
                                            <li>
                                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" data-bs-toggle="modal" data-bs-target="#viewNbaModal{{ $nba->id }}">
                                                    <i class="bi bi-eye text-info"></i> View Details
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" data-bs-toggle="modal" data-bs-target="#editNbaModal{{ $nba->id }}">
                                                    <i class="bi bi-pencil text-primary"></i> Edit Account
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $nba->id }}">
                                                    <i class="bi bi-key text-warning"></i> Reset Password
                                                </button>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.nba-coordinators.toggleStatus', $nba) }}" method="POST" class="m-0 p-0">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3">
                                                        @if(($nba->status ?? 'active') === 'active')
                                                            <i class="bi bi-person-x text-secondary"></i> Deactivate
                                                        @else
                                                            <i class="bi bi-person-check text-success"></i> Activate
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('admin.nba-coordinators.destroy', $nba) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this NBA Coordinator account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2 px-3">
                                                        <i class="bi bi-trash"></i> Delete Account
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- View Details Modal -->
                                    <div class="modal fade text-start" id="viewNbaModal{{ $nba->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold" style="color: var(--navy);">NBA Coordinator Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="text-center mb-3">
                                                        <div class="avatar-lg rounded-circle bg-info bg-opacity-10 text-info fw-bold mx-auto d-flex align-items-center justify-content-center border" style="width:64px; height:64px; font-size: 1.5rem;">
                                                            {{ strtoupper(substr($nba->name, 0, 1)) }}
                                                        </div>
                                                        <h5 class="fw-bold mt-2 mb-0">{{ $nba->name }}</h5>
                                                        <span class="badge bg-info text-white rounded-pill mt-1">NBA Coordinator</span>
                                                    </div>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Email</span>
                                                            <span class="fw-medium text-dark">{{ $nba->email }}</span>
                                                        </li>
                                                        <li class="list-group-item px-0">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="text-muted">Password</span>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="fw-medium text-danger" id="nbaPass{{ $nba->id }}" style="letter-spacing: 0.5px;">
                                                                        {{ $nba->plain_password ?? '••••••••' }}
                                                                    </span>
                                                                    @if($nba->plain_password)
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary border-0 p-0 px-1" onclick="copyCredential(this, '{{ $nba->plain_password }}')" title="Copy Password">
                                                                            <i class="bi bi-clipboard small"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if(!$nba->plain_password)
                                                                <small class="text-muted fst-italic d-block mt-1" style="font-size: 0.7rem;">Password was set before credential tracking was enabled. Reset to view.</small>
                                                            @endif
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Department</span>
                                                            <span class="fw-medium text-dark">{{ $nba->department->name ?? 'Unassigned' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Status</span>
                                                            <span class="fw-medium">{{ ucfirst($nba->status ?? 'active') }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span class="text-muted">Created Date</span>
                                                            <span class="fw-medium">{{ $nba->created_at ? $nba->created_at->format('M d, Y') : '-' }}</span>
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
                                    <div class="modal fade text-start" id="editNbaModal{{ $nba->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.nba-coordinators.update', $nba) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Edit NBA Coordinator Account</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Full Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $nba->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control" value="{{ $nba->email }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Department</label>
                                                            <select name="department_id" class="form-select" required>
                                                                @foreach($departments as $dept)
                                                                    <option value="{{ $dept->id }}" {{ $nba->department_id == $dept->id ? 'selected' : '' }}>
                                                                        {{ $dept->name }} ({{ $dept->code }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Account Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="active" {{ ($nba->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ ($nba->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">New Password (optional)</label>
                                                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-info text-white rounded-pill">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reset Password Modal -->
                                    <div class="modal fade text-start" id="resetPasswordModal{{ $nba->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form action="{{ route('admin.nba-coordinators.resetPassword', $nba) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Reset Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted">Set a new password for <strong>{{ $nba->name }}</strong>.</p>
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
                                <td colspan="6" class="text-center py-4 text-muted">No NBA Coordinator accounts found. Click "Add NBA Coordinator" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addNbaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.nba-coordinators.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" style="color: var(--navy);">Add NBA Coordinator Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Dr. Jane Smith" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="nba.cse@psgitech.ac.in" required>
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
                        <button type="submit" class="btn btn-info text-white rounded-pill">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
ss