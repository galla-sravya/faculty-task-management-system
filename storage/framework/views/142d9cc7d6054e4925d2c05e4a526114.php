<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-award me-2 text-warning"></i>NBA Coordinator Dashboard</h1>
        <p class="text-muted small mb-0">Overview of NBA Accreditation Tasks, Document Reviews, Deadlines & Meetings</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('nba.tasks.create')); ?>" class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Create NBA Task
        </a>
        <a href="<?php echo e(route('nba.meetings.create')); ?>" class="btn btn-sm btn-outline-navy fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-calendar-plus"></i> Schedule NBA Meeting
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-primary">
            <div class="text-muted small text-uppercase fw-semibold mb-1">My NBA Tasks</div>
            <div class="h3 fw-bold mb-0 text-navy"><?php echo e($stats['total']); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-warning">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Pending</div>
            <div class="h3 fw-bold mb-0 text-warning"><?php echo e($stats['pending']); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-info">
            <div class="text-muted small text-uppercase fw-semibold mb-1">In Progress</div>
            <div class="h3 fw-bold mb-0 text-info"><?php echo e($stats['in_progress']); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-purple">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Pending Review</div>
            <div class="h3 fw-bold mb-0 text-primary"><?php echo e($stats['pending_review']); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-success">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Completed</div>
            <div class="h3 fw-bold mb-0 text-success"><?php echo e($stats['completed']); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-danger">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Overdue</div>
            <div class="h3 fw-bold mb-0 text-danger"><?php echo e($stats['overdue']); ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: My NBA Tasks & Documents Awaiting Approval -->
    <div class="col-lg-8">
        <!-- Documents Awaiting Approval -->
        <?php if($documentsAwaitingApproval->count() > 0): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check text-warning fs-5"></i> Documents Awaiting Review
                </h6>
                <span class="badge bg-warning text-dark"><?php echo e($documentsAwaitingApproval->count()); ?> Pending</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-3">Document</th>
                                <th>Task</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $documentsAwaitingApproval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="ps-3 fw-semibold text-navy">
                                    <i class="bi bi-file-earmark-pdf me-1 text-danger"></i> <?php echo e($doc->file_name); ?>

                                </td>
                                <td>
                                    <a href="<?php echo e(route('nba.tasks.show', $doc->task_id)); ?>" class="text-decoration-none text-dark fw-medium">
                                        <?php echo e(Str::limit($doc->task->title ?? 'NBA Task', 30)); ?>

                                    </a>
                                </td>
                                <td><?php echo e($doc->user->name ?? 'Faculty'); ?></td>
                                <td><?php echo e($doc->created_at->format('M d, Y')); ?></td>
                                <td class="pe-3 text-end">
                                    <a href="<?php echo e(route('nba.tasks.show', $doc->task_id)); ?>" class="btn btn-sm btn-psg-primary py-1 px-2" style="font-size:0.78rem;">
                                        Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- My NBA Tasks Table -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-list-task text-primary fs-5"></i> My NBA Tasks
                </h6>
                <a href="<?php echo e(route('nba.tasks.index')); ?>" class="btn btn-sm btn-link text-navy fw-semibold p-0 text-decoration-none">View All &rarr;</a>
            </div>
            <div class="card-body p-0">
                <?php if($myTasks->isEmpty()): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No NBA tasks found. <a href="<?php echo e(route('nba.tasks.create')); ?>" class="text-navy fw-semibold">Create an NBA Task</a> to get started.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-3">Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Deadline</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $myTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="<?php echo e(route('nba.tasks.show', $task)); ?>" class="fw-semibold text-navy text-decoration-none">
                                            <?php echo e(Str::limit($task->title, 35)); ?>

                                        </a>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            Owner: <?php echo e($task->creator->name ?? 'Self'); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($task->priority_color); ?>-subtle text-<?php echo e($task->priority_color); ?> text-capitalize">
                                            <?php echo e($task->priority); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($task->status_color); ?>-subtle text-<?php echo e($task->status_color); ?> text-capitalize">
                                            <?php echo e(str_replace('_', ' ', $task->status)); ?>

                                        </span>
                                    </td>
                                    <td style="width: 120px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo e($task->overall_progress); ?>%"></div>
                                            </div>
                                            <span class="text-muted fw-medium" style="font-size:0.75rem;"><?php echo e($task->overall_progress); ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="<?php echo e($task->is_overdue ? 'text-danger fw-bold' : ''); ?>">
                                            <?php echo e($task->deadline->format('M d, Y')); ?>

                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="<?php echo e(route('nba.tasks.show', $task)); ?>" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size:0.78rem;">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Meetings, Deadlines, Notifications -->
    <div class="col-lg-4">
        <!-- Upcoming Deadlines -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-danger fs-5"></i> Upcoming Deadlines (7 Days)
                </h6>
            </div>
            <div class="card-body p-3">
                <?php if($upcomingDeadlines->isEmpty()): ?>
                    <div class="text-muted small text-center py-3">No tasks due within the next 7 days.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php $__currentLoopData = $upcomingDeadlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ut): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?php echo e(route('nba.tasks.show', $ut)); ?>" class="text-dark fw-semibold text-decoration-none small d-block">
                                    <?php echo e(Str::limit($ut->title, 28)); ?>

                                </a>
                                <span class="badge bg-<?php echo e($ut->priority_color); ?>-subtle text-<?php echo e($ut->priority_color); ?>" style="font-size:0.65rem;">
                                    <?php echo e(ucfirst($ut->priority)); ?>

                                </span>
                            </div>
                            <span class="badge bg-danger-subtle text-danger small">
                                <?php echo e($ut->deadline->format('M d')); ?>

                            </span>
                        </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Meetings -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event text-success fs-5"></i> My NBA Meetings
                </h6>
                <a href="<?php echo e(route('nba.meetings.index')); ?>" class="btn btn-sm btn-link text-navy fw-semibold p-0 text-decoration-none">View All</a>
            </div>
            <div class="card-body p-3">
                <?php if($myMeetings->isEmpty()): ?>
                    <div class="text-muted small text-center py-3">No NBA meetings scheduled.</div>
                <?php else: ?>
                    <?php $__currentLoopData = $myMeetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border-bottom pb-2 mb-2 last-border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <a href="<?php echo e(route('nba.meetings.show', $m)); ?>" class="fw-semibold text-dark text-decoration-none small">
                                <?php echo e($m->title); ?>

                            </a>
                            <span class="badge bg-<?php echo e($m->status === 'completed' ? 'success' : 'primary'); ?>-subtle text-<?php echo e($m->status === 'completed' ? 'success' : 'primary'); ?>" style="font-size:0.65rem;">
                                <?php echo e(ucfirst($m->status)); ?>

                            </span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            <i class="bi bi-clock me-1"></i> <?php echo e($m->scheduled_at->format('M d, Y g:i A')); ?>

                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Notifications -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-bell text-warning fs-5"></i> My Notifications
                </h6>
                <a href="<?php echo e(route('notifications.index')); ?>" class="btn btn-sm btn-link text-navy fw-semibold p-0 text-decoration-none">All</a>
            </div>
            <div class="card-body p-3">
                <?php if($myNotifications->isEmpty()): ?>
                    <div class="text-muted small text-center py-3">No recent notifications.</div>
                <?php else: ?>
                    <?php $__currentLoopData = $myNotifications->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="d-flex gap-2 align-items-start border-bottom pb-2 mb-2">
                        <i class="bi bi-info-circle text-primary mt-1" style="font-size:0.85rem;"></i>
                        <div>
                            <div class="small text-dark"><?php echo e($note->message); ?></div>
                            <div class="text-muted" style="font-size:0.7rem;"><?php echo e($note->sent_at?->diffForHumans()); ?></div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/nba/dashboard.blade.php ENDPATH**/ ?>