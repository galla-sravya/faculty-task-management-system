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
        <form action="{{ route('nba.tasks.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-navy">Task Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Prepare NBA Criterion 5 Documentation" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @error
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
                    @error('deadline') <div class="invalid-feedback">{{ $message }}</div> @error
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
                    <select name="assignees[]" class="form-select @error('assignees') is-invalid @enderror" multiple required style="height: 140px;">
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}" {{ in_array($faculty->id, old('assignees', [])) ? 'selected' : '' }}>
                                {{ $faculty->name }} ({{ $faculty->designation }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted">Hold Ctrl / Cmd to select multiple faculty members.</div>
                    @error('assignees') <div class="invalid-feedback">{{ $message }}</div> @error
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('nba.tasks.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-psg-primary px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Create & Assign Task</button>
            </div>
        </form>
    </div>
</div>
@endsection
