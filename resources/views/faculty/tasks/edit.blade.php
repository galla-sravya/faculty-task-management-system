@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Edit Task</h1>
        <p class="text-muted small mb-0">Update details for task: {{ $task->title }}</p>
    </div>
    <a href="{{ route('faculty.tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Task Details
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-pencil-square me-2" style="color: var(--gold);"></i>Edit Task Details
                </h6>
            </div>
            <div class="card-body p-4">
                <form id="editTaskForm" action="{{ route('faculty.tasks.update', $task) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control border @error('title') is-invalid @enderror" style="border-color: var(--border) !important;" required value="{{ old('title', $task->title) }}" placeholder="Enter task title...">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Category</label>
                            <input type="text" name="category" class="form-control border @error('category') is-invalid @enderror" style="border-color: var(--border) !important;" value="{{ old('category', $task->category) }}" placeholder="e.g. Lab, Curriculum, Event...">
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="description" class="form-control border @error('description') is-invalid @enderror" style="border-color: var(--border) !important;" rows="4" placeholder="Describe the task expectations...">{{ old('description', $task->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select border @error('priority') is-invalid @enderror" style="border-color: var(--border) !important;" required>
                                <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>🟢 Low</option>
                                <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                                <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>🟠 High</option>
                                <option value="urgent" {{ old('priority', $task->priority) === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="deadline" class="form-control border @error('deadline') is-invalid @enderror" style="border-color: var(--border) !important;" required value="{{ old('deadline', $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '') }}">
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Related Meeting <span class="text-muted fw-normal">(Optional)</span></label>
                            <select name="meeting_id" class="form-select border @error('meeting_id') is-invalid @enderror" style="border-color: var(--border) !important;">
                                <option value="">-- None / Independent Task --</option>
                                @foreach($meetings as $m)
                                    <option value="{{ $m->id }}" {{ old('meeting_id', $task->meeting_id) == $m->id ? 'selected' : '' }}>
                                        {{ $m->title }} ({{ $m->scheduled_at->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('meeting_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Assign To Faculty <span class="text-danger">*</span></label>
                        @include('components.faculty-selector', ['faculties' => $faculties, 'selected' => $selectedAssignees])
                        @error('assignees')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('faculty.tasks.show', $task) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn text-white fw-semibold px-4 shadow-sm" style="background-color: var(--navy);">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
