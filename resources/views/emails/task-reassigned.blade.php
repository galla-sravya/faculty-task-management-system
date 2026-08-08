<x-mail::message>
@if(file_exists(public_path('images/psg-logo-email.png')))
<div style="text-align: center; margin-bottom: 20px;">
<img src="cid:psg-logo.png" alt="PSG Logo" style="max-height: 80px;">
</div>
@endif

# Task Reassigned: {{ $task->title }}

Hello **{{ $newAssignee->name }}**,

A task has been reassigned to you by **{{ $actor->name }}**. 
(It was previously assigned to {{ $oldAssignee->name }}).

**Task Details:**
- **Title:** {{ $task->title }}
- **Priority:** {{ ucfirst($task->priority) }}
- **Deadline:** {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y h:i A') : 'No Deadline' }}
@if($reason)
- **Reason for Reassignment:** {{ $reason }}
@endif

<x-mail::button :url="route('login')">
View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
