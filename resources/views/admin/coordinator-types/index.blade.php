@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-2 pb-3 mb-4 border-bottom">
    <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">Manage Coordinator Types</h1>
    <a href="{{ route('admin.coordinator-types.create') }}" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: var(--navy);">
        <i class="bi bi-plus-lg"></i> Create Type
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4 py-3">Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr>
                        <td class="ps-4 fw-semibold text-dark">{{ $type->name }}</td>
                        <td><span class="badge bg-secondary">{{ $type->slug }}</span></td>
                        <td>{{ Str::limit($type->description, 50) }}</td>
                        <td>{{ $type->creator->name ?? 'System' }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.coordinator-types.edit', $type) }}" class="btn btn-sm btn-outline-primary py-1 px-2">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No coordinator types found. Create one to get started.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
