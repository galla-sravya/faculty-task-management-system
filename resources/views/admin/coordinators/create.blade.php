@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-2 pb-3 mb-4 border-bottom">
    <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">Add Coordinator</h1>
    <a href="{{ route('admin.coordinators.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.coordinators.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role_selection" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="hod" {{ old('role_selection') == 'hod' ? 'selected' : '' }}>HOD</option>
                        <option value="nba_coordinator" {{ old('role_selection') == 'nba_coordinator' ? 'selected' : '' }}>NBA Coordinator</option>
                        <optgroup label="Dynamic Coordinators">
                            @foreach($coordinatorTypes as $type)
                                <option value="{{ $type->id }}" {{ old('role_selection') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" class="btn text-white fw-medium px-4" style="background-color: var(--navy);">Create Coordinator</button>
            </div>
        </form>
    </div>
</div>
@endsection
