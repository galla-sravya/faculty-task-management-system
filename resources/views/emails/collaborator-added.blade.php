<x-mail::message>
# Added as Collaborator: {{ $task->title }}

You have been added as a collaborator to this task by {{ $actor->name }}.

**Priority:** {{ ucfirst($task->priority) }}  
**Assigned To You:** {{ now()->format('M d, Y h:i A') }}  
**Deadline:** {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y h:i A') }}

@if($task->description)
**Description:**  
{{ $task->description }}
@endif

<x-mail::button :url="route('tasks.view', $task)">
View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
