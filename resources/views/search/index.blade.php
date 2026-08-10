@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Global Search Results</h1>
        <p class="text-muted small mb-0">Results for query: "<strong class="text-dark">{{ $query }}</strong>"</p>
    </div>
</div>

<!-- Search Input -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-3">
        <form action="{{ route('global.search') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control border" value="{{ $query }}" placeholder="Search by faculty, task, category, priority, meeting, status..." required>
            <button type="submit" class="btn text-white fw-medium px-4 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-search me-1"></i>Search
            </button>
        </form>
    </div>
</div>

<!-- Tasks Results -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-clipboard-data me-2" style="color: var(--gold);"></i>Matching Tasks ({{ $tasks->count() }})
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.72rem;">
                    <tr>
                        <th class="ps-3 py-3">Task Title</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Deadline</th>
                        <th class="pe-3 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $t)
                    <tr>
                        <td class="ps-3 fw-semibold text-dark">{{ $t->title }}</td>
                        <td><span class="badge rounded-pill bg-{{ $t->priority_color }} px-2 py-1">{{ ucfirst($t->priority) }}</span></td>
                        <td><span class="badge rounded-pill bg-{{ $t->status_color }} px-2 py-1">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span></td>
                        <td class="small text-muted">{{ $t->deadline->format('M d, Y') }}</td>
                        <td class="pe-3 text-end">
                            <a href="{{ auth()->user()->isHod() ? route('hod.tasks.show', $t) : route('faculty.tasks.show', $t) }}" class="btn btn-sm btn-outline-primary fw-medium" style="font-size:0.75rem;">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-3 text-muted">No matching tasks found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Faculty Results -->
@if(auth()->user()->isHod() && $faculties->isNotEmpty())
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-people me-2" style="color: var(--maroon);"></i>Matching Faculty ({{ $faculties->count() }})
        </h6>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            @foreach($faculties as $f)
                <div class="col-md-4">
                    <a href="{{ route('hod.faculty.performance', $f) }}" class="text-decoration-none d-block">
                        <div class="p-3 border rounded bg-light d-flex align-items-center gap-3 transition-hover shadow-sm-hover">
                            <img src="{{ $f->profile_photo_url }}" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;border:2px solid var(--navy);">
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:0.9rem;">{{ $f->name }}</div>
                                <div class="text-muted small" style="font-size:0.75rem;">{{ $f->designation ?? 'Faculty' }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection
