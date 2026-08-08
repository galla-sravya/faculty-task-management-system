@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-2 pb-3 mb-4 border-bottom">
    <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">Edit Coordinator Type</h1>
    <a href="{{ route('admin.coordinator-types.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.coordinator-types.update', $coordinatorType) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Coordinator Type Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $coordinatorType->name) }}">
                <div class="form-text">A unique URL-safe slug will be automatically updated from this name.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $coordinatorType->description) }}</textarea>
            </div>
            
            <div class="mt-4 pt-3 border-top text-end d-flex justify-content-end gap-2">
                <button type="submit" class="btn text-white fw-medium px-4" style="background-color: var(--navy);">Update Type</button>
            </div>
        </form>
        
        <form action="{{ route('admin.coordinator-types.destroy', $coordinatorType) }}" method="POST" class="mt-3 text-end" onsubmit="return confirm('Are you sure you want to delete this coordinator type? All users assigned to this type will no longer function correctly.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete Coordinator Type</button>
        </form>
    </div>
</div>
@endsection
