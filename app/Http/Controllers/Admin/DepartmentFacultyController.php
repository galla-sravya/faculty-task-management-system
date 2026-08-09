<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DepartmentFacultyController extends Controller
{
    public function store(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'faculty',
            'department_id' => $department->id,
            'designation' => $request->designation ?? 'Assistant Professor',
            'phone' => $request->phone,
            'specialization' => $request->specialization,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.departments.show', $department)->with('success', 'Faculty member added successfully.');
    }

    public function update(Request $request, Department $department, User $faculty)
    {
        if ($faculty->role !== 'faculty') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($faculty->id)],
            'department_id' => 'required|exists:departments,id',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $targetDeptId = $request->department_id;

        $faculty->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $targetDeptId,
            'designation' => $request->designation,
            'phone' => $request->phone,
            'specialization' => $request->specialization,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $faculty->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.departments.show', $targetDeptId)->with('success', 'Faculty member details updated successfully.');
    }

    public function toggleStatus(Department $department, User $faculty)
    {
        if ($faculty->role !== 'faculty') {
            abort(404);
        }

        $newStatus = ($faculty->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $faculty->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'Faculty account activated.' : 'Faculty account deactivated.';
        return redirect()->route('admin.departments.show', $department)->with('success', $msg);
    }

    public function resetPassword(Request $request, Department $department, User $faculty)
    {
        if ($faculty->role !== 'faculty') {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $faculty->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.departments.show', $department)->with('success', "Password reset successfully for {$faculty->name}.");
    }

    public function destroy(Department $department, User $faculty)
    {
        if ($faculty->role !== 'faculty') {
            abort(404);
        }

        $faculty->delete();

        return redirect()->route('admin.departments.show', $department)->with('success', 'Faculty member removed successfully.');
    }
}
