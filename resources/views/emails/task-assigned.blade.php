<x-mail::message>
# New Task Assigned: {{ $task->title }}

You have been assigned a new task by your Head of Department.

**Priority:** {{ ucfirst($task->priority) }}  
**Assigned Date:** {{ \Carbon\Carbon::parse($task->created_at)->format('M d, Y h:i A') }}  
**Deadline:** {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y h:i A') }}

@if($task->description)
**Description:**  
{{ $task->description }}
@endif

<x-mail::button :url="route('faculty.tasks.show', $task)">
View Task & Update Progress
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
