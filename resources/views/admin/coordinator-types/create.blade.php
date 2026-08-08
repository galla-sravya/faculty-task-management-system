@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-2 pb-3 mb-4 border-bottom">
    <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">Create Coordinator Type</h1>
    <a href="{{ route('admin.coordinator-types.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.coordinator-types.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Coordinator Type Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="e.g. Placement Coordinator">
                <div class="form-text">A unique URL-safe slug will be automatically generated from this name.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            
            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" class="btn text-white fw-medium px-4" style="background-color: var(--navy);">Create Type</button>
            </div>
        </form>
    </div>
</div>
@endsection
