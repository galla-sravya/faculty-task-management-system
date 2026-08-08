@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Admin Dashboard</h1>
        <p class="text-muted small mb-0">System-wide overview of Coordinators, Faculty, and Tasks</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('admin.coordinators.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-person-plus"></i> Add Coordinator
        </a>
        <a href="{{ route('admin.coordinator-types.index') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--maroon);">
            <i class="bi bi-gear"></i> Manage Types
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <x-stat-card label="HOD Accounts" :value="$totalHod" color="navy" icon="bi bi-person-badge" link="{{ route('admin.coordinators.index') }}" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-stat-card label="NBA Coords" :value="$totalNba" color="warning" icon="bi bi-award" link="{{ route('admin.coordinators.index') }}" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-stat-card label="Dynamic Coords" :value="$totalCoordinators" color="success" icon="bi bi-people" link="{{ route('admin.coordinators.index') }}" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-stat-card label="Total Faculty" :value="$totalFaculty" color="info" icon="bi bi-person-workspace" link="#" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-stat-card label="Departments" :value="$totalDepartments" color="purple" icon="bi bi-building" link="#" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-stat-card label="Total Tasks" :value="$totalTasks" color="danger" icon="bi bi-list-task" link="#" />
    </div>
</div>
@endsection
