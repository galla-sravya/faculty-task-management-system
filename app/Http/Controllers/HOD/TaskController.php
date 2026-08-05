<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssigned;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('department_id', auth()->user()->department_id)
            ->with(['assignees', 'meeting']);

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('deadline', '<', now())
                      ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(10);
        
        return view('hod.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->get();
        $meetings = Meeting::where('department_id', auth()->user()->department_id)
            ->latest()
            ->take(10)
            ->get();
            
        return view('hod.tasks.create', compact('faculties', 'meetings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'deadline' => 'required|date|after:today',
            'assignees' => 'required|array',
            'assignees.*' => 'exists:users,id',
            'meeting_id' => 'nullable|exists:meetings,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'deadline' => $validated['deadline'],
            'created_by' => auth()->id(),
            'department_id' => auth()->user()->department_id,
            'meeting_id' => $validated['meeting_id'] ?? null,
            'status' => 'pending',
        ]);

        $attachData = [];
        foreach ($validated['assignees'] as $userId) {
            $attachData[$userId] = [
                'status'              => 'pending',
                'progress_percentage' => 0,
                'role'                => 'owner',
                'assigned_by'         => auth()->id(),
                'assigned_at'         => now(),
            ];

            \App\Models\TaskAssignment::create([
                'task_id'     => $task->id,
                'faculty_id'  => $userId,
                'assigned_by' => auth()->id(),
                'role'        => 'owner',
                'status'      => 'pending',
                'assigned_at' => now(),
            ]);
        }
        $task->assignees()->attach($attachData);

        // Activity log entry
        \App\Models\TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => auth()->id(),
            'action'      => 'assigned',
            'description' => 'Task created and assigned by ' . auth()->user()->name . '.',
        ]);

        // Send notifications
        foreach ($validated['assignees'] as $userId) {
            NotificationLog::create([
                'user_id' => $userId,
                'type' => 'task_assigned',
                'reference_id' => $task->id,
                'reference_type' => Task::class,
                'message' => 'You have been assigned a new task: ' . $task->title,
                'sent_at' => now(),
            ]);
            
            Mail::to(User::find($userId))->send(new TaskAssigned($task));
        }

        return redirect()->route('hod.tasks.index')->with('success', 'Task created and assigned successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task->load(['creator', 'meeting', 'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at')]);
        return view('hod.tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->get();
        $meetings = Meeting::where('department_id', auth()->user()->department_id)
            ->latest()
            ->take(10)
            ->get();
            
        $task->load('assignees');
        $selectedAssignees = $task->assignees->pluck('id')->toArray();
            
        return view('hod.tasks.edit', compact('task', 'faculties', 'meetings', 'selectedAssignees'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'deadline' => 'required|date',
            'assignees' => 'required|array',
            'assignees.*' => 'exists:users,id',
            'meeting_id' => 'nullable|exists:meetings,id',
        ]);

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'deadline' => $validated['deadline'],
            'meeting_id' => $validated['meeting_id'] ?? null,
        ]);

        // Sync will detach removed ones and attach new ones, but we want to preserve pivot data for existing ones
        // A better approach is to syncWithoutDetaching and manual detach
        $currentAssignees = $task->assignees->pluck('id')->toArray();
        $newAssignees = $validated['assignees'];
        
        $toAttach = array_diff($newAssignees, $currentAssignees);
        $toDetach = array_diff($currentAssignees, $newAssignees);
        
        if (!empty($toDetach)) {
            $task->assignees()->detach($toDetach);
        }
        
        if (!empty($toAttach)) {
            $attachData = [];
            foreach ($toAttach as $id) {
                $attachData[$id] = ['status' => 'pending', 'progress_percentage' => 0];
                
                NotificationLog::create([
                    'user_id' => $id,
                    'type' => 'task_assigned',
                    'reference_id' => $task->id,
                    'reference_type' => Task::class,
                    'message' => 'You have been assigned to a task: ' . $task->title,
                    'sent_at' => now(),
                ]);

                Mail::to(User::find($id))->send(new TaskAssigned($task));
            }
            $task->assignees()->attach($attachData);
        }

        return redirect()->route('hod.tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return redirect()->route('hod.tasks.index')->with('success', 'Task deleted successfully.');
    }
}
