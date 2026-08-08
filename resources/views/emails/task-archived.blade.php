<x-mail::message>
# Task Archived: {{ $task->title }}

The following task you were assigned to has been archived by {{ $actor->name }}.

**Priority:** {{ ucfirst($task->priority) }}  

You no longer need to submit progress updates for this task unless it is restored.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
