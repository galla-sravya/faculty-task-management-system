@props(['task'])
@php
    $sd = $task->smart_deadline;
@endphp
<span class="{{ $sd['badge_class'] }} px-2 py-1 shadow-sm" style="font-size: 0.75rem; white-space: nowrap;">
    <i class="bi bi-calendar3 me-1"></i>{{ $sd['label'] }}
</span>
