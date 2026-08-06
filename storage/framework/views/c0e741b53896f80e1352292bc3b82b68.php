<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['task']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['task']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\TimelineFormatter;
    use Carbon\Carbon;

    $assignees = $task->assignees;
    $total = $assignees->count();
?>

<?php if($total > 1): ?>
<div class="card bg-white shadow-sm border-0 mt-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-signpost-2 me-2" style="color: var(--gold);"></i>Collaboration Timeline
        </h6>
    </div>
    <div class="card-body p-4">
        <div class="task-timeline">
            <?php $__currentLoopData = $assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
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


                ?>

                
                <div class="timeline-step position-relative" style="padding-left: 44px; padding-bottom: <?php echo e($index < $total - 1 ? '0' : '0'); ?>px;">
                    
                    <?php if($index < $total - 1): ?>
                        <div class="timeline-track" style="position: absolute; left: 15px; top: 34px; bottom: -24px; width: 3px; background-color: #e9ecef;"></div>
                    <?php endif; ?>

                    
                    <div class="timeline-node-circle" style="position: absolute; left: 4px; top: 6px; width: 26px; height: 26px; border-radius: 50%; background: #fff; border: 3px solid <?php echo e($nodeColor); ?>; display: flex; align-items: center; justify-content: center; z-index: 2;">
                        <i class="bi <?php echo e($nodeIcon); ?>" style="font-size: 0.7rem; color: <?php echo e($nodeColor); ?>;"></i>
                    </div>

                    
                    <div class="timeline-card bg-white border rounded-3 p-3 mb-0 shadow-sm" style="border-color: #e9ecef !important;">
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?php echo e($assignee->profile_photo_url); ?>" alt="<?php echo e($assignee->name); ?>"
                                     class="rounded-circle shadow-sm object-fit-cover"
                                     style="width: 36px; height: 36px; border: 2px solid var(--navy); flex-shrink: 0;"
                                     title="<?php echo e($assignee->name); ?>">
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;"><?php echo e($assignee->name); ?></div>
                                    <div class="text-muted" style="font-size: 0.72rem;"><?php echo e($assignee->designation ?? 'Faculty'); ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-2 py-1" style="background-color: <?php echo e($badgeBg); ?>; color: <?php echo e($badgeText); ?>; font-weight: 600; font-size: 0.68rem;">
                                    <?php echo e($badgeLabel); ?>

                                </span>
                                <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.65rem; font-weight: 500;">
                                    Step <?php echo e($index + 1); ?> of <?php echo e($total); ?>

                                </span>
                            </div>
                        </div>

                        
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="progress flex-grow-1" style="height: 6px; background-color: #e9ecef; border-radius: 3px;">
                                <div class="progress-bar" style="width: <?php echo e($progress); ?>%; background-color: <?php echo e($barColor); ?>; border-radius: 3px; transition: width 0.4s ease;"></div>
                            </div>
                            <span class="small fw-semibold" style="min-width: 35px; font-size: 0.78rem; color: <?php echo e($badgeText); ?>;"><?php echo e($progress); ?>%</span>
                        </div>

                        <?php if($remarks): ?>
                            <div class="rounded p-2 mb-2" style="background-color: #f8f9fa; border-left: 3px solid <?php echo e($nodeColor); ?>; font-size: 0.8rem;">
                                <i class="bi bi-chat-left-text me-1 text-muted"></i>
                                <span class="text-muted"><?php echo e($remarks); ?></span>
                            </div>
                        <?php endif; ?>

                        
                        <?php
                            $assigneeDocs = \App\Models\TaskDocument::where('task_id', $task->id)->where('user_id', $assignee->id)->get();
                        ?>
                        <?php if($assigneeDocs->isNotEmpty()): ?>
                            <div class="mb-3">
                                <div class="small fw-semibold text-muted mb-1" style="font-size: 0.72rem; text-transform: uppercase;">Attachments</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $assigneeDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e(Storage::url($doc->file_path)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="font-size: 0.7rem; border-radius: 4px; padding: 2px 6px;">
                                            <i class="bi bi-file-earmark-text me-1 text-primary"></i> 
                                            <span class="text-truncate" style="max-width: 130px;"><?php echo e($doc->file_name); ?></span>
                                            <span class="text-muted ms-1" style="font-size: 0.6rem;"><?php echo e($doc->created_at->format('M d, H:i')); ?></span>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <div class="d-flex flex-wrap align-items-center gap-2" style="font-size: 0.75rem;">
                            <span class="text-muted">
                                <i class="bi bi-clock me-1"></i><?php echo e($timestampLabel); ?>: <?php echo e($timestampValue); ?>

                            </span>
                            <span class="badge rounded-pill border bg-light" style="font-size: 0.68rem; color: #555;">
                                <i class="bi bi-stopwatch me-1"></i><?php echo e($durationText); ?>

                            </span>
                        </div>
                    </div>
                </div>

                
                <?php if($index < $total - 1): ?>
                    <?php
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
                    ?>
                    <div class="position-relative" style="padding-left: 44px; padding-top: 4px; padding-bottom: 4px;">
                        
                        <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 3px; background-color: #e9ecef;"></div>
                        <div class="text-center">
                            <span class="badge rounded-pill border bg-white text-muted shadow-sm" style="font-size: 0.7rem; font-weight: 500; padding: 4px 12px;">
                                <i class="bi bi-arrow-down-circle me-1"></i><?php echo e($connectorText); ?>

                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\Project\activity-monitor-main\resources\views/components/task-timeline.blade.php ENDPATH**/ ?>