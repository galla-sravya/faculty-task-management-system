<x-mail::message>
@php
    $isNba = $task instanceof \App\Models\NBATask;
    $taskUrl = $isNba ? route('faculty.nba-tasks.show', $task) : route('faculty.tasks.show', $task);
    $pivot = $recipient->assignedTasks()->where('tasks.id', $task->id)->first()?->pivot 
             ?? ($isNba ? $recipient->assignedNbaTasks()->where('nba_tasks.id', $task->id)->first()?->pivot : null);
    $currentProgress = $pivot ? (int) $pivot->progress_percentage : 0;
    $currentStatus = $pivot ? ucfirst(str_replace('_', ' ', $pivot->status)) : 'Pending';
@endphp

# {{ $isOverdue ? '⚠️ Overdue Task Reminder' : '⏰ Task Progress Reminder' }}

Dear **{{ $recipient->name }}**,

@if($isAutomated)
This is an automated **9:00 AM daily reminder** that your assigned task is past its deadline and still marked as incomplete.
@else
You have received a reminder from **{{ $sender->name }}** regarding your assigned task.
@endif

---

### Task Details:
- **Task Title:** {{ $task->title }}
- **Created / Assigned By:** {{ $sender->name }} ({{ $sender->designation ?? 'Faculty' }})
- **Priority:** {{ ucfirst($task->priority) }}
- **Deadline:** {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y \a\t g:i A') }}
@if($isOverdue)
- **Status:** <span style="color: #c62828; font-weight: bold;">OVERDUE by {{ $task->deadline->diffForHumans() }}</span>
@endif
- **Your Current Progress:** {{ $currentProgress }}% ({{ $currentStatus }})

@if(!empty($customMessage))
### 💬 Note from {{ $sender->name }}:
> *{{ $customMessage }}*
@endif

@if(!empty($task->description))
### Description:
{{ $task->description }}
@endif

<x-mail::button :url="$taskUrl" color="primary">
View Task & Submit Progress
</x-mail::button>

Please review the task, upload required documents, and update your progress at the earliest.

Best regards,  
**{{ config('app.name', 'PSG iTech Activity Monitoring System') }}**
</x-mail::message>
