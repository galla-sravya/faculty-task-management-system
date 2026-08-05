@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Add Faculty Member</h1>
        <p class="text-muted small mb-0">Register a new faculty member in your department</p>
    </div>
    <a href="{{ route('hod.faculty.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Faculty List
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-person-plus me-2" style="color: var(--gold);"></i>Faculty Account Details
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hod.faculty.store') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control border @error('name') is-invalid @enderror"
                                   style="border-color: var(--border) !important;" required value="{{ old('name') }}" placeholder="e.g. Dr. A. Sharma">
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control border @error('designation') is-invalid @enderror"
                                   style="border-color: var(--border) !important;" required value="{{ old('designation') }}" placeholder="e.g. Assistant Professor">
                            @error('designation')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control border @error('email') is-invalid @enderror"
                                   style="border-color: var(--border) !important;" required value="{{ old('email') }}" placeholder="name@psgitech.ac.in">
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Phone Number</label>
                            <input type="text" name="phone" class="form-control border @error('phone') is-invalid @enderror"
                                   style="border-color: var(--border) !important;" value="{{ old('phone') }}" placeholder="+91 9876543210">
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control border @error('password') is-invalid @enderror"
                                   style="border-color: var(--border) !important;" required placeholder="Minimum 8 characters">
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control border"
                                   style="border-color: var(--border) !important;" required placeholder="Re-enter password">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                            <i class="bi bi-person-check me-1"></i> Register Faculty Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
