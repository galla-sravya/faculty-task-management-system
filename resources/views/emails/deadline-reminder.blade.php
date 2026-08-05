<x-mail::message>
@if($stage === 'morning')
# Morning Reminder: Task Due Today
@elseif($stage === 'evening')
# Evening Reminder: Task Due Today
@else
# Task Deadline Reminder (48 Hours)
@endif

Task: **{{ $task->title }}**

@if($stage === 'morning')
This is a morning reminder that your assigned task is due today.
@elseif($stage === 'evening')
This is an evening reminder that your assigned task is due today. Please make sure to complete and update your task status.
@else
This is a reminder that your assigned task is due in 48 hours.
@endif

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
