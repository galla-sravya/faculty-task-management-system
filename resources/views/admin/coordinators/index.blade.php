@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-2 pb-3 mb-4 border-bottom">
    <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">Manage Coordinators</h1>
    <a href="{{ route('admin.coordinators.create') }}" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: var(--navy);">
        <i class="bi bi-plus-lg"></i> Add Coordinator
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
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coordinators as $coord)
                    <tr>
                        <td class="ps-4 fw-semibold text-dark">{{ $coord->name }}</td>
                        <td>{{ $coord->email }}</td>
                        <td>
                            @if($coord->role === 'hod')
                                <span class="badge bg-navy-subtle text-navy">HOD</span>
                            @elseif($coord->role === 'nba_coordinator')
                                <span class="badge bg-warning-subtle text-warning-emphasis">NBA Coordinator</span>
                            @elseif($coord->role === 'coordinator')
                                <span class="badge bg-success-subtle text-success">{{ $coord->coordinatorType->name ?? 'Coordinator' }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $coord->role }}</span>
                            @endif
                        </td>
                        <td>{{ $coord->department->name ?? 'N/A' }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.coordinators.edit', $coord) }}" class="btn btn-sm btn-outline-primary py-1 px-2">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No coordinators found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
