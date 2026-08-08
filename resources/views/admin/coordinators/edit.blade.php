@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-2 pb-3 mb-4 border-bottom">
    <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">Edit Coordinator</h1>
    <a href="{{ route('admin.coordinators.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.coordinators.update', $coordinator) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $coordinator->name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email', $coordinator->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (old('department_id', $coordinator->department_id) == $dept->id) ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role</label>
                    @php
                        $currentRole = $coordinator->role === 'coordinator' ? $coordinator->coordinator_type_id : $coordinator->role;
                    @endphp
                    <select name="role_selection" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="hod" {{ old('role_selection', $currentRole) == 'hod' ? 'selected' : '' }}>HOD</option>
                        <option value="nba_coordinator" {{ old('role_selection', $currentRole) == 'nba_coordinator' ? 'selected' : '' }}>NBA Coordinator</option>
                        <optgroup label="Dynamic Coordinators">
                            @foreach($coordinatorTypes as $type)
                                <option value="{{ $type->id }}" {{ old('role_selection', $currentRole) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="mt-4 pt-3 border-top text-end d-flex justify-content-end gap-2">
                <button type="submit" class="btn text-white fw-medium px-4" style="background-color: var(--navy);">Update Coordinator</button>
            </div>
        </form>
        <form action="{{ route('admin.coordinators.destroy', $coordinator) }}" method="POST" class="mt-3 text-end" onsubmit="return confirm('Are you sure you want to delete this coordinator?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete Coordinator</button>
        </form>
    </div>
</div>
@endsection
