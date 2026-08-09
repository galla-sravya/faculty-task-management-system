<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class HodController extends Controller
{
    public function index()
    {
        $hods = User::where('role', 'hod')
            ->with('department')
            ->latest()
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('admin.hods.index', compact('hods', 'departments'));
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

        $hod = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'hod',
            'department_id' => $request->department_id,
            'status' => $request->status,
        ]);

        // Link department to HOD if department has no HOD set
        $dept = Department::find($request->department_id);
        if ($dept) {
            $dept->update(['hod_id' => $hod->id]);
        }

        return redirect()->route('admin.hods.index')->with('success', 'HOD Account created successfully.');
    }

    public function update(Request $request, User $hod)
    {
        if ($hod->role !== 'hod') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($hod->id)],
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ]);

        $hod->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $hod->update(['password' => Hash::make($request->password)]);
        }

        // Link department to HOD
        $dept = Department::find($request->department_id);
        if ($dept) {
            $dept->update(['hod_id' => $hod->id]);
        }

        return redirect()->route('admin.hods.index')->with('success', 'HOD Account updated successfully.');
    }

    public function toggleStatus(User $hod)
    {
        if ($hod->role !== 'hod') {
            abort(404);
        }

        $newStatus = ($hod->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $hod->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'HOD account activated.' : 'HOD account deactivated.';
        return redirect()->route('admin.hods.index')->with('success', $msg);
    }

    public function resetPassword(Request $request, User $hod)
    {
        if ($hod->role !== 'hod') {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $hod->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.hods.index')->with('success', "Password reset successfully for {$hod->name}.");
    }

    public function destroy(User $hod)
    {
        if ($hod->role !== 'hod') {
            abort(404);
        }

        // Clear department HOD link if this user was department HOD
        Department::where('hod_id', $hod->id)->update(['hod_id' => null]);

        $hod->delete();

        return redirect()->route('admin.hods.index')->with('success', 'HOD Account deleted successfully.');
    }
}
