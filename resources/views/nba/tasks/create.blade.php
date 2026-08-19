@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-plus-circle me-2 text-primary"></i>Create NBA Task</h1>
        <p class="text-muted small mb-0">Create and assign a new NBA Accreditation task to faculty</p>
    </div>
    <div>
        <a href="{{ route('nba.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to Tasks</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form id="createTaskForm" action="{{ route('nba.tasks.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-navy">Task Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Prepare NBA Criterion 5 Documentation" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-navy">Category</label>
                    <input type="text" name="category" class="form-control" value="NBA Accreditation" readonly>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-navy">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed requirements and guidelines for this NBA task...">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-navy">Priority <span class="text-danger">*</span></label>
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-navy">Deadline <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline') }}" required>
                    @error('deadline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-navy">Related Meeting (Optional)</label>
                    <select name="meeting_id" class="form-select">
                        <option value="">-- Select Meeting --</option>
                        @foreach($meetings as $m)
                            <option value="{{ $m->id }}" {{ old('meeting_id') == $m->id ? 'selected' : '' }}>{{ $m->title }} ({{ $m->scheduled_at->format('M d, Y') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-navy">Assign To Faculty <span class="text-danger">*</span></label>
                    @include('components.faculty-selector', ['faculties' => $faculties])
                    @error('assignees') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('nba.tasks.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="button" id="submitTaskBtn" class="btn btn-psg-primary px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Create & Assign Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Faculty Workload Info Modal -->
<div class="modal fade" id="workloadInfoModal" tabindex="-1" aria-labelledby="workloadInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-top: 4px solid var(--navy) !important;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold" id="workloadInfoModalLabel" style="color: var(--navy);">
            <i class="bi bi-info-circle-fill me-2"></i>Faculty Workload Info
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3 pb-2">
        <p class="mb-3 text-secondary">Just a quick note about the current workload for the selected faculty:</p>
        <ul id="workloadInfoList" class="mb-4" style="color: #555;"></ul>
        <p class="mb-0 fw-medium text-dark">Would you like to proceed with assigning this task?</p>
      </div>
      <div class="modal-footer border-top-0 pt-0 pb-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Go Back</button>
        <button type="button" class="btn fw-semibold text-white" style="background-color: var(--navy);" id="confirmAssignBtn">Proceed & Assign</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitTaskBtn');
    const form = document.getElementById('createTaskForm');
    const confirmBtn = document.getElementById('confirmAssignBtn');
    const infoModal = new bootstrap.Modal(document.getElementById('workloadInfoModal'));
    const infoList = document.getElementById('workloadInfoList');

    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Basic HTML5 validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const assigneeInputs = form.querySelectorAll('input[name="assignees[]"]:checked');
        const deadlineInput = form.querySelector('input[name="deadline"]');
        
        const assignees = Array.from(assigneeInputs).map(opt => opt.value);
        const deadline = deadlineInput.value;

        if (assignees.length === 0 || !deadline) {
            form.submit();
            return;
        }

        // Disable button and show spinner
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Checking...';

        fetch('{{ route('tasks.checkWorkload') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                assignees: assignees,
                deadline: deadline
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            if (data.warnings && data.warnings.length > 0) {
                infoList.innerHTML = '';
                data.warnings.forEach(warning => {
                    const li = document.createElement('li');
                    li.innerHTML = warning;
                    li.className = 'mb-1';
                    infoList.appendChild(li);
                });
                infoModal.show();
            } else {
                form.submit();
            }
        })
        .catch(error => {
            console.error('Error checking workload:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            // On error, just submit the form anyway
            form.submit();
        });
    });

    confirmBtn.addEventListener('click', function() {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Assigning...';
        form.submit();
    });
});
</script>
@endsection
