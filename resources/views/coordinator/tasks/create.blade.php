@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Create New Task</h1>
        <p class="text-muted small mb-0">Assign a new task to faculty members</p>
    </div>
    <a href="{{ route('coordinator.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Tasks
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-pencil-square me-2" style="color: var(--gold);"></i>Task Details
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('coordinator.tasks.store') }}" method="POST" class="workload-check-form">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control border" style="border-color: var(--border) !important;" required value="{{ old('title') }}" placeholder="Enter task title...">
                        @error('title')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="description" class="form-control border" style="border-color: var(--border) !important;" rows="4" placeholder="Describe the task...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Priority</label>
                            <select name="priority" class="form-select border" style="border-color: var(--border) !important;">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>🟢 Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>🟠 High</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="deadline" class="form-control border" style="border-color: var(--border) !important;" required value="{{ old('deadline') }}">
                            @error('deadline')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Assign To Faculty <span class="text-danger">*</span></label>
                        @include('components.faculty-selector', ['faculties' => $faculties])
                        @error('assignees')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                            <i class="bi bi-send me-1"></i> Create Task & Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
