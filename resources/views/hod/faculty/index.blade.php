@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Faculty Members</h1>
        <p class="text-muted small mb-0">Overview and performance metrics of department faculty members</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('hod.faculty.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-person-plus"></i> Add Faculty
        </a>
    </div>
</div>

<div class="row g-4">
    @forelse($faculties as $faculty)
        @php
            $rate = $faculty->total_tasks > 0 ? round(($faculty->completed_tasks_count / $faculty->total_tasks) * 100) : 0;
            $deptName = $faculty->department->name ?? auth()->user()->department->name ?? 'Department';
        @endphp
        <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('hod.faculty.performance', $faculty) }}" class="text-decoration-none">
                <div class="card bg-white shadow-sm border-0 h-100 stat-card" style="border-radius: var(--radius, 8px);">
                    <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                        <!-- Faculty Photo -->
                        <img src="{{ $faculty->profile_photo_url }}" alt="{{ $faculty->name }}" class="rounded-circle mb-3 shadow-sm object-fit-cover" style="width: 58px; height: 58px; border: 2px solid var(--navy);">

                        <!-- Name, Designation & Email -->
                        <h6 class="fw-bold mb-1 text-dark text-truncate w-100" style="font-size: 1.05rem;" title="{{ $faculty->name }}">{{ $faculty->name }}</h6>
                        <p class="text-muted small mb-1 text-truncate w-100" style="font-size: 0.78rem;" title="{{ $faculty->designation ?? 'Faculty' }}">
                            <i class="bi bi-mortarboard me-1"></i>{{ $faculty->designation ?? 'Faculty' }}
                        </p>
                        <p class="text-muted small mb-2 text-truncate w-100" style="font-size: 0.73rem;" title="{{ $faculty->email }}">
                            <i class="bi bi-envelope me-1"></i>{{ $faculty->email }}
                        </p>

                        <!-- Progress Ring (100px) -->
                        <div class="my-2">
                            <x-progress-ring :percent="$rate" label="Completed" :size="100" />
                        </div>

                        <!-- Task Stats Bar -->
                        <div class="w-100 mt-3 pt-3 border-top d-flex justify-content-around text-muted small" style="border-color: var(--border) !important;">
                            <div>
                                <span class="d-block fw-bold text-dark fs-6">{{ $faculty->total_tasks }}</span>
                                <span class="text-uppercase" style="font-size: 0.65rem;">Assigned</span>
                            </div>
                            <div class="border-start" style="border-color: var(--border) !important;"></div>
                            <div>
                                <span class="d-block fw-bold text-success fs-6">{{ $faculty->completed_tasks_count }}</span>
                                <span class="text-uppercase" style="font-size: 0.65rem;">Done</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="card bg-white shadow-sm border-0 py-5 text-center" style="border-radius: var(--radius, 8px);">
                <i class="bi bi-people fs-1 text-muted mb-2"></i>
                <p class="text-muted mb-0">No faculty members found in this department.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
