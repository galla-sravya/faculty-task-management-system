<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\CoordinatorType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CoordinatorController extends Controller
{
    public function index()
    {
        $coordinators = User::whereIn('role', ['hod', 'nba_coordinator', 'coordinator'])
            ->with(['department', 'coordinatorType'])
            ->latest()
            ->get();
            
        return view('admin.coordinators.index', compact('coordinators'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $coordinatorTypes = CoordinatorType::orderBy('name')->get();
        return view('admin.coordinators.create', compact('departments', 'coordinatorTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_selection' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $role = $request->role_selection;
        $coordinatorTypeId = null;

        if (!in_array($role, ['hod', 'nba_coordinator'])) {
            // It must be a coordinator_type_id
            $coordinatorTypeId = $role;
            $role = 'coordinator';
            $request->validate([
                'role_selection' => 'exists:coordinator_types,id',
            ]);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'department_id' => $request->department_id,
            'coordinator_type_id' => $coordinatorTypeId,
        ]);

        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinator created successfully.');
    }

    public function edit(User $coordinator)
    {
        $departments = Department::orderBy('name')->get();
        $coordinatorTypes = CoordinatorType::orderBy('name')->get();
        return view('admin.coordinators.edit', compact('coordinator', 'departments', 'coordinatorTypes'));
    }

    public function update(Request $request, User $coordinator)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($coordinator->id)],
            'role_selection' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $role = $request->role_selection;
        $coordinatorTypeId = null;

        if (!in_array($role, ['hod', 'nba_coordinator'])) {
            $coordinatorTypeId = $role;
            $role = 'coordinator';
            $request->validate([
                'role_selection' => 'exists:coordinator_types,id',
            ]);
        }

        $coordinator->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'department_id' => $request->department_id,
            'coordinator_type_id' => $coordinatorTypeId,
        ]);

        if ($request->filled('password')) {
            $coordinator->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinator updated successfully.');
    }

    public function destroy(User $coordinator)
    {
        $coordinator->delete();
        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinator deleted successfully.');
    }
}
