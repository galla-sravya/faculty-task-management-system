@props(['task'])

@php
    use App\Support\TimelineFormatter;
    use Carbon\Carbon;

    $assignees = $task->assignees;
    $total = $assignees->count();
@endphp

@if($total > 1)
<div class="card bg-white shadow-sm border-0 mt-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-signpost-2 me-2" style="color: var(--gold);"></i>Collaboration Timeline
        </h6>
    </div>
    <div class="card-body p-4">
        <div class="task-timeline">
            @foreach($assignees as $index => $assignee)
                @php
                    $status   = $assignee->pivot->status;
                    $progress = (int) $assignee->pivot->progress_percentage;
                    $remarks  = $assignee->pivot->remarks;
                    $completedAt = $assignee->pivot->completed_at ? Carbon::parse($assignee->pivot->completed_at) : null;
                    $pivotCreatedAt = $assignee->pivot->created_at ? Carbon::parse($assignee->pivot->created_at) : null;
                    $pivotUpdatedAt = $assignee->pivot->updated_at ? Carbon::parse($assignee->pivot->updated_at) : null;

                    // Determine node style
                    if ($status === 'completed') {
                        $nodeColor = 'var(--success, #2e7d32)';
                        $nodeIcon  = 'bi-check-circle-fill';
                        $badgeBg   = '#e8f5e9';
                        $badgeText = '#2e7d32';
                        $badgeLabel = 'Completed';
                    } elseif ($status === 'in_progress') {
                        $nodeColor = '#2196F3';
                        $nodeIcon  = 'bi-hourglass-split';
                        $badgeBg   = '#e3f2fd';
                        $badgeText = '#1565c0';
                        $badgeLabel = 'In Progress';
                    } else {
                        $nodeColor = 'var(--warning, #f57f17)';
                        $nodeIcon  = 'bi-circle';
                        $badgeBg   = '#fff8e1';
                        $badgeText = '#f57f17';
                        $badgeLabel = 'Pending';
                    }

                    // Duration text
                    if ($status === 'completed' && $pivotCreatedAt && $completedAt) {
                        $durationText = 'Completed in ' . TimelineFormatter::format($pivotCreatedAt, $completedAt);
                    } elseif ($status === 'in_progress' && $pivotCreatedAt) {
                        $durationText = TimelineFormatter::format($pivotCreatedAt) . ' in progress';
                    } else {
                        $durationText = 'Awaiting start (' . ($pivotCreatedAt ? TimelineFormatter::format($pivotCreatedAt) : '0h 0m') . ' idle)';
                    }

                    // Timestamp text
                    if ($status === 'completed' && $completedAt) {
                        $timestampLabel = 'Submitted';
                        $timestampValue = $completedAt->format('M d, Y \a\t g:i A');
                    } elseif ($status === 'in_progress' && $pivotUpdatedAt) {
                        $timestampLabel = 'Last Updated';
                        $timestampValue = $pivotUpdatedAt->format('M d, Y \a\t g:i A');
                    } else {
                        $timestampLabel = 'Assigned';
                        $timestampValue = $pivotCreatedAt ? $pivotCreatedAt->format('M d, Y') : 'N/A';
                    }

                    // Progress bar color
                    if ($progress >= 100)     { $barColor = '#2e7d32'; }
                    elseif ($progress >= 50)  { $barColor = '#1565c0'; }
                    elseif ($progress > 0)    { $barColor = 'var(--gold, #d4a017)'; }
                    else                      { $barColor = '#e9ecef'; }


                @endphp

                {{-- Timeline node --}}
                <div class="timeline-step position-relative" style="padding-left: 44px; padding-bottom: {{ $index < $total - 1 ? '0' : '0' }}px;">
                    {{-- Vertical track line (skip for last item) --}}
                    @if($index < $total - 1)
                        <div class="timeline-track" style="position: absolute; left: 15px; top: 34px; bottom: -24px; width: 3px; background-color: #e9ecef;"></div>
                    @endif

                    {{-- Node circle --}}
                    <div class="timeline-node-circle" style="position: absolute; left: 4px; top: 6px; width: 26px; height: 26px; border-radius: 50%; background: #fff; border: 3px solid {{ $nodeColor }}; display: flex; align-items: center; justify-content: center; z-index: 2;">
                        <i class="bi {{ $nodeIcon }}" style="font-size: 0.7rem; color: {{ $nodeColor }};"></i>
                    </div>

                    {{-- Card --}}
                    <div class="timeline-card bg-white border rounded-3 p-3 mb-0 shadow-sm" style="border-color: #e9ecef !important;">
                        {{-- Header row: avatar + name + status badge + step counter --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $assignee->profile_photo_url }}" alt="{{ $assignee->name }}"
                                     class="rounded-circle shadow-sm object-fit-cover"
                                     style="width: 36px; height: 36px; border: 2px solid var(--navy); flex-shrink: 0;"
                                     title="{{ $assignee->name }}">
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $assignee->name }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $assignee->designation ?? 'Faculty' }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $badgeBg }}; color: {{ $badgeText }}; font-weight: 600; font-size: 0.68rem;">
                                    {{ $badgeLabel }}
                                </span>
                                <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.65rem; font-weight: 500;">
                                    Step {{ $index + 1 }} of {{ $total }}
                                </span>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="progress flex-grow-1" style="height: 6px; background-color: #e9ecef; border-radius: 3px;">
                                <div class="progress-bar" style="width: {{ $progress }}%; background-color: {{ $barColor }}; border-radius: 3px; transition: width 0.4s ease;"></div>
                            </div>
                            <span class="small fw-semibold" style="min-width: 35px; font-size: 0.78rem; color: {{ $badgeText }};">{{ $progress }}%</span>
                        </div>

                        @if($remarks)
                            <div class="rounded p-2 mb-2" style="background-color: #f8f9fa; border-left: 3px solid {{ $nodeColor }}; font-size: 0.8rem;">
                                <i class="bi bi-chat-left-text me-1 text-muted"></i>
                                <span class="text-muted">{{ $remarks }}</span>
                            </div>
                        @endif

                        {{-- Attachments --}}
                        @php
                            $assigneeDocs = \App\Models\TaskDocument::where('task_id', $task->id)->where('user_id', $assignee->id)->get();
                        @endphp
                        @if($assigneeDocs->isNotEmpty())
                            <div class="mb-3">
                                <div class="small fw-semibold text-muted mb-1" style="font-size: 0.72rem; text-transform: uppercase;">Attachments</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($assigneeDocs as $doc)
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="font-size: 0.7rem; border-radius: 4px; padding: 2px 6px;">
                                            <i class="bi bi-file-earmark-text me-1 text-primary"></i> 
                                            <span class="text-truncate" style="max-width: 130px;">{{ $doc->file_name }}</span>
                                            <span class="text-muted ms-1" style="font-size: 0.6rem;">{{ $doc->created_at->format('M d, H:i') }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Timestamp + duration --}}
                        <div class="d-flex flex-wrap align-items-center gap-2" style="font-size: 0.75rem;">
                            <span class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $timestampLabel }}: {{ $timestampValue }}
                            </span>
                            <span class="badge rounded-pill border bg-light" style="font-size: 0.68rem; color: #555;">
                                <i class="bi bi-stopwatch me-1"></i>{{ $durationText }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Connector gap between this step and the next --}}
                @if($index < $total - 1)
                    @php
                        $nextAssignee = $assignees[$index + 1];
                        $nextPivotCreatedAt = $nextAssignee->pivot->created_at ? Carbon::parse($nextAssignee->pivot->created_at) : null;
                        $nextPivotUpdatedAt = $nextAssignee->pivot->updated_at ? Carbon::parse($nextAssignee->pivot->updated_at) : null;

                        // "Last activity" of current faculty
                        if ($status === 'completed' && $completedAt) {
                            $currentLastActivity = $completedAt;
                        } elseif ($pivotUpdatedAt) {
                            $currentLastActivity = $pivotUpdatedAt;
                        } else {
                            $currentLastActivity = $pivotCreatedAt;
                        }

                        // "First activity" of next faculty
                        $nextStatus = $nextAssignee->pivot->status;
                        if ($nextStatus === 'pending') {
                            // Next hasn't started — show "waiting" measured to now
                            $connectorText = '→ waiting ' . ($currentLastActivity ? TimelineFormatter::format($currentLastActivity) : '0h 0m');
                        } else {
                            // Next has activity — show the gap
                            $nextFirstActivity = $nextPivotCreatedAt ?? $nextPivotUpdatedAt;
                            if ($currentLastActivity && $nextFirstActivity) {
                                $connectorText = '→ ' . TimelineFormatter::format($currentLastActivity, $nextFirstActivity) . ' later';
                            } else {
                                $connectorText = '→ gap unknown';
                            }
                        }
                    @endphp
                    <div class="position-relative" style="padding-left: 44px; padding-top: 4px; padding-bottom: 4px;">
                        {{-- Continue the vertical line through the connector --}}
                        <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 3px; background-color: #e9ecef;"></div>
                        <div class="text-center">
                            <span class="badge rounded-pill border bg-white text-muted shadow-sm" style="font-size: 0.7rem; font-weight: 500; padding: 4px 12px;">
                                <i class="bi bi-arrow-down-circle me-1"></i>{{ $connectorText }}
                            </span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif
