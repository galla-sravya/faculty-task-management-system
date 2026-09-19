<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\CoordinatorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DynamicCoordinatorController extends Controller
{
    public function index()
    {
        $coordinators = User::where('role', 'coordinator')
            ->with(['department', 'coordinatorType'])
            ->latest()
            ->get();

        $departments = Department::orderBy('name')->get();
        $coordinatorTypes = CoordinatorType::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();

        return view('admin.dynamic-coordinators.index', compact('coordinators', 'departments', 'coordinatorTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'department_id' => 'required|exists:departments,id',
            'coordinator_type_id' => 'required|exists:coordinator_types,id',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'coordinator',
            'department_id' => $request->department_id,
            'coordinator_type_id' => $request->coordinator_type_id,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.dynamic-coordinators.index')->with('success', 'Dynamic Coordinator account created successfully.');
    }

    public function update(Request $request, User $dynamicCoordinator)
    {
        if ($dynamicCoordinator->role !== 'coordinator') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($dynamicCoordinator->id)],
            'department_id' => 'required|exists:departments,id',
            'coordinator_type_id' => 'required|exists:coordinator_types,id',
            'status' => 'required|in:active,inactive',
        ]);

        $dynamicCoordinator->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'coordinator_type_id' => $request->coordinator_type_id,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $dynamicCoordinator->update([
                'password' => Hash::make($request->password),
                'plain_password' => $request->password,
            ]);
        }

        return redirect()->route('admin.dynamic-coordinators.index')->with('success', 'Dynamic Coordinator account updated successfully.');
    }

    public function toggleStatus(User $dynamicCoordinator)
    {
        if ($dynamicCoordinator->role !== 'coordinator') {
            abort(404);
        }

        $newStatus = ($dynamicCoordinator->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $dynamicCoordinator->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'Dynamic Coordinator account activated.' : 'Dynamic Coordinator account deactivated.';
        return redirect()->route('admin.dynamic-coordinators.index')->with('success', $msg);
    }

    public function resetPassword(Request $request, User $dynamicCoordinator)
    {
        if ($dynamicCoordinator->role !== 'coordinator') {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $dynamicCoordinator->update([
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
        ]);

        return redirect()->route('admin.dynamic-coordinators.index')->with('success', "Password reset successfully for {$dynamicCoordinator->name}.");
    }

    public function destroy(User $dynamicCoordinator)
    {
        if ($dynamicCoordinator->role !== 'coordinator') {
            abort(404);
        }

        $dynamicCoordinator->delete();

        return redirect()->route('admin.dynamic-coordinators.index')->with('success', 'Dynamic Coordinator account deleted successfully.');
    }
}
