<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['hod', 'nbaCoordinator'])
            ->withCount('faculty')
            ->orderBy('name')
            ->get();

        $hods = User::where('role', 'hod')->orderBy('name')->get();

        return view('admin.departments.index', compact('departments', 'hods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:20|unique:departments,code',
            'status' => 'required|in:active,inactive',
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'status' => $request->status,
            'hod_id' => $request->hod_id,
        ]);

        if ($request->filled('hod_id')) {
            User::where('id', $request->hod_id)->update(['department_id' => $department->id]);
        }

        return redirect()->route('admin.departments.index')->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load(['hod', 'nbaCoordinator']);

        $facultyMembers = User::where('department_id', $department->id)
            ->where('role', 'faculty')
            ->latest()
            ->get();

        $allDepartments = Department::orderBy('name')->get();

        return view('admin.departments.show', compact('department', 'facultyMembers', 'allDepartments'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department->id)],
            'status' => 'required|in:active,inactive',
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $department->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'status' => $request->status,
            'hod_id' => $request->hod_id,
        ]);

        if ($request->filled('hod_id')) {
            User::where('id', $request->hod_id)->update(['department_id' => $department->id]);
        }

        return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
    }

    public function toggleStatus(Department $department)
    {
        $newStatus = ($department->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $department->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'Department activated.' : 'Department deactivated.';
        return redirect()->back()->with('success', $msg);
    }

    public function destroy(Department $department)
    {
        // Safety check before deletion
        $usersCount = User::where('department_id', $department->id)->count();

        if ($usersCount > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', "Cannot delete department '{$department->name}' because it contains {$usersCount} associated account(s). Please reassign or remove accounts first.");
        }

        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
    }

}
