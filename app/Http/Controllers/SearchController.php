<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
use App\Models\TaskDocument;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->input('q'));
        $departmentId = auth()->user()->department_id;

        if (empty($query)) {
            return view('search.index', [
                'query'     => '',
                'tasks'     => collect(),
                'faculties' => collect(),
                'meetings'  => collect(),
                'documents' => collect(),
            ]);
        }

        $user = auth()->user();

        if ($user->isNbaCoordinator()) {
            $userId = $user->id;

            $tasks = Task::where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('user_id', $userId));
            })->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('priority', 'like', "%{$query}%")
                  ->orWhere('status', 'like', "%{$query}%");
            })->with(['assignees', 'creator'])->latest()->get();

            $faculties = collect(); // NBA Coordinator does not see faculty list search

            $meetings = Meeting::where(function ($q) use ($userId) {
                $q->where('organized_by', $userId)
                  ->orWhereHas('attendees', fn ($aq) => $aq->where('user_id', $userId));
            })->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('venue', 'like', "%{$query}%");
            })->latest()->get();

            $documents = TaskDocument::whereHas('task', function ($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('user_id', $userId));
            })->where(function ($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%")
                  ->orWhere('remarks', 'like', "%{$query}%")
                  ->orWhere('review_comments', 'like', "%{$query}%");
            })->with(['task', 'user'])->latest()->get();
        } else {
            // Search Tasks
            $tasks = Task::where('department_id', $departmentId)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('category', 'like', "%{$query}%")
                      ->orWhere('priority', 'like', "%{$query}%")
                      ->orWhere('status', 'like', "%{$query}%");
                })
                ->with(['assignees', 'creator'])
                ->latest()
                ->get();

            // Search Faculty
            $faculties = User::where('department_id', $departmentId)
                ->where('role', 'faculty')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('designation', 'like', "%{$query}%")
                      ->orWhere('specialization', 'like', "%{$query}%");
                })
                ->get();

            // Search Meetings
            $meetings = Meeting::where('department_id', $departmentId)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('venue', 'like', "%{$query}%");
                })
                ->latest()
                ->get();

            // Search Documents
            $documents = TaskDocument::whereHas('task', fn ($q) => $q->where('department_id', $departmentId))
                ->where(function ($q) use ($query) {
                    $q->where('file_name', 'like', "%{$query}%")
                      ->orWhere('remarks', 'like', "%{$query}%")
                      ->orWhere('review_comments', 'like', "%{$query}%");
                })
                ->with(['task', 'user'])
                ->latest()
                ->get();
        }

        return view('search.index', compact('query', 'tasks', 'faculties', 'meetings', 'documents'));
    }
}
