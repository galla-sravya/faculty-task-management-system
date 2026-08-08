<x-mail::message>
@if($stage === '50pct')
# Halfway There: 50% Time Used
@elseif($stage === '75pct')
# Time Running Out: 75% Time Used
@else
# Task Deadline Reminder
@endif

Task: **{{ $task->title }}**

@if($stage === '50pct')
You're halfway through the time you had for this task — check in on your progress.
@elseif($stage === '75pct')
Only 25% of your available time is left before the deadline — please wrap up soon.
@else
This is a reminder regarding your assigned task deadline.
@endif

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
