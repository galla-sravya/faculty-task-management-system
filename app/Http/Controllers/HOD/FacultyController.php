<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->withCount(['assignedTasks as completed_tasks_count' => function ($query) {
                $query->where('task_user.status', 'completed');
            }])
            ->withCount('assignedTasks as total_tasks')
            ->get();
            
        return view('hod.faculty.index', compact('faculties'));
    }

    public function create()
    {
        return view('hod.faculty.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'designation' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'faculty',
            'department_id' => auth()->user()->department_id,
            'phone' => $validated['phone'],
            'designation' => $validated['designation'],
        ]);

        return redirect()->route('hod.faculty.index')->with('success', 'Faculty added successfully.');
    }

    public function performance(User $faculty)
    {
        if ($faculty->department_id !== auth()->user()->department_id || $faculty->role !== 'faculty') {
            abort(403);
        }

        $faculty->load(['assignedTasks' => function ($query) {
            $query->orderBy('deadline', 'desc');
        }]);
        
        $totalTasks = $faculty->assignedTasks->count();
        $completedTasks = $faculty->assignedTasks->where('pivot.status', 'completed')->count();
        $inProgressTasks = $faculty->assignedTasks->where('pivot.status', 'in_progress')->count();
        $pendingTasks = $faculty->assignedTasks->where('pivot.status', 'pending')->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return view('hod.faculty.performance', compact(
            'faculty', 'totalTasks', 'completedTasks', 'inProgressTasks', 'pendingTasks', 'completionRate'
        ));
    }
}
