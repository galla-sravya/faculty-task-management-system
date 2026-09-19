<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class NbaCoordinatorController extends Controller
{
    public function index()
    {
        $nbaCoordinators = User::where('role', 'nba_coordinator')
            ->with('department')
            ->latest()
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('admin.nba-coordinators.index', compact('nbaCoordinators', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'nba_coordinator',
            'department_id' => $request->department_id,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.nba-coordinators.index')->with([
            'success' => 'NBA Coordinator account created successfully.',
            'created_credentials' => [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ],
        ]);
    }

    public function update(Request $request, User $nbaCoordinator)
    {
        if ($nbaCoordinator->role !== 'nba_coordinator') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($nbaCoordinator->id)],
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ]);

        $nbaCoordinator->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $nbaCoordinator->update([
                'password' => Hash::make($request->password),
                'plain_password' => $request->password,
            ]);
        }

        return redirect()->route('admin.nba-coordinators.index')->with('success', 'NBA Coordinator account updated successfully.');
    }

    public function toggleStatus(User $nbaCoordinator)
    {
        if ($nbaCoordinator->role !== 'nba_coordinator') {
            abort(404);
        }

        $newStatus = ($nbaCoordinator->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $nbaCoordinator->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'NBA Coordinator account activated.' : 'NBA Coordinator account deactivated.';
        return redirect()->route('admin.nba-coordinators.index')->with('success', $msg);
    }

    public function resetPassword(Request $request, User $nbaCoordinator)
    {
        if ($nbaCoordinator->role !== 'nba_coordinator') {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $nbaCoordinator->update([
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
        ]);

        return redirect()->route('admin.nba-coordinators.index')->with([
            'success' => "Password reset successfully for {$nbaCoordinator->name}.",
            'created_credentials' => [
                'name' => $nbaCoordinator->name,
                'email' => $nbaCoordinator->email,
                'password' => $request->password,
            ],
        ]);
    }

    public function destroy(User $nbaCoordinator)
    {
        if ($nbaCoordinator->role !== 'nba_coordinator') {
            abort(404);
        }

        $nbaCoordinator->delete();

        return redirect()->route('admin.nba-coordinators.index')->with('success', 'NBA Coordinator account deleted successfully.');
    }
}
