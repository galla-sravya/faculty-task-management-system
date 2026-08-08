<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use App\Services\NotificationService;

class TaskController extends Controller
{
    protected function getOwnerRole()
    {
        return auth()->user()->coordinatorType->slug;
    }

    public function index(Request $request)
    {
        $departmentId = auth()->user()->department_id;
        $ownerRole = $this->getOwnerRole();

        $tasks = Task::where('department_id', $departmentId)
            ->where('owner_role', $ownerRole)
            ->with(['assignees', 'creator'])
            ->latest()
            ->paginate(15);
            
        return view('coordinator.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $departmentId = auth()->user()->department_id;
        $faculties = User::where('department_id', $departmentId)->where('role', 'faculty')->orderBy('name')->get();
        return view('coordinator.tasks.create', compact('faculties'));
    }

    public function store(Request $request, NotificationService $notificationService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'category' => $request->category,
            'deadline' => $request->deadline,
            'status' => 'pending',
            'department_id' => auth()->user()->department_id,
            'created_by' => auth()->id(),
            'owner_role' => $this->getOwnerRole(),
        ]);

        $task->assignees()->attach($request->assignees, [
            'role' => 'owner',
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        // Send notifications
        foreach ($request->assignees as $assigneeId) {
            $user = User::find($assigneeId);
            if ($user) {
                $notificationService->sendTaskAssignedNotification($task, collect([$user]));
            }
        }

        return redirect()->route('coordinator.dashboard')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        // Ensure isolation
        if ($task->owner_role !== $this->getOwnerRole() || $task->department_id !== auth()->user()->department_id) {
            abort(403);
        }
        
        $task->load(['assignees', 'creator', 'documents.user', 'activities.user', 'comments.user']);
        $faculties = User::where('department_id', auth()->user()->department_id)->where('role', 'faculty')->orderBy('name')->get();
        
        return view('coordinator.tasks.show', compact('task', 'faculties'));
    }
}
