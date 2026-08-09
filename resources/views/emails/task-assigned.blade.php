<x-mail::message>
# New Task Assigned: {{ $task->title }}

@php
    $assignerRole = 'Head of Department';
    if ($task->creator) {
        if ($task->creator->role === 'nba_coordinator') {
            $assignerRole = 'NBA Coordinator';
        } elseif ($task->creator->role === 'coordinator' && $task->creator->coordinatorType) {
            $assignerRole = $task->creator->coordinatorType->name;
        } elseif ($task->creator->role === 'faculty') {
            $assignerRole = 'Faculty Member';
        }
    }
@endphp

You have been assigned a new task by your {{ $assignerRole }}.

**Priority:** {{ ucfirst($task->priority) }}  
**Assigned Date:** {{ \Carbon\Carbon::parse($task->created_at)->format('M d, Y h:i A') }}  
**Deadline:** {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y h:i A') }}

@if($task->description)
**Description:**  
{{ $task->description }}
@endif

<x-mail::button :url="route('tasks.view', $task)">
View Task & Update Progress
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
