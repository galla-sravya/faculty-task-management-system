<?php $__env->startSection('content'); ?>
<?php
    $totalAssigned = $pendingTasks + $inProgressTasks + $completedTasks;
    $completionRate = $totalAssigned > 0 ? round(($completedTasks / $totalAssigned) * 100) : 0;
    $overdueCount = $upcomingDeadlines->filter(fn($task) => $task->deadline && $task->deadline->isPast())->count();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Faculty Dashboard</h1>
        <p class="text-muted small mb-0">Overview of your assigned tasks, personal progress, and department meetings</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="<?php echo e(route('faculty.tasks.index')); ?>" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-list-task"></i> View My Tasks
        </a>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Assigned Tasks','value' => $totalAssigned,'color' => 'navy','icon' => 'bi bi-clipboard-check','link' => ''.e(route('faculty.tasks.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Assigned Tasks','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalAssigned),'color' => 'navy','icon' => 'bi bi-clipboard-check','link' => ''.e(route('faculty.tasks.index')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
    </div>
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Completed Tasks','value' => $completedTasks,'color' => 'success','icon' => 'bi bi-check-circle','link' => ''.e(route('faculty.tasks.index', ['status' => 'completed'])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Completed Tasks','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($completedTasks),'color' => 'success','icon' => 'bi bi-check-circle','link' => ''.e(route('faculty.tasks.index', ['status' => 'completed'])).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
    </div>
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'In Progress','value' => $inProgressTasks,'color' => 'warning','icon' => 'bi bi-clock-history','link' => ''.e(route('faculty.tasks.index', ['status' => 'in_progress'])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'In Progress','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inProgressTasks),'color' => 'warning','icon' => 'bi bi-clock-history','link' => ''.e(route('faculty.tasks.index', ['status' => 'in_progress'])).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
    </div>
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Overdue Tasks','value' => $overdueCount,'color' => 'danger','icon' => 'bi bi-exclamation-triangle','link' => ''.e(route('faculty.tasks.index', ['status' => 'overdue'])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Overdue Tasks','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overdueCount),'color' => 'danger','icon' => 'bi bi-exclamation-triangle','link' => ''.e(route('faculty.tasks.index', ['status' => 'overdue'])).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
    </div>
</div>

<!-- Progress Ring & Upcoming Deadlines Row -->
<div class="row g-4 mb-4">
    <!-- Personal Completion Ring -->
    <div class="col-lg-5 col-xl-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-speedometer2 me-2" style="color: var(--gold);"></i>Personal Work Completion
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                <?php if (isset($component)) { $__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-ring','data' => ['percent' => $completionRate,'label' => 'Personal completion rate','size' => 180]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-ring'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($completionRate),'label' => 'Personal completion rate','size' => 180]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c)): ?>
<?php $attributes = $__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c; ?>
<?php unset($__attributesOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c)): ?>
<?php $component = $__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c; ?>
<?php unset($__componentOriginal9c06ee1e0a144c1c56b582ceb4e4ea6c); ?>
<?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines Table -->
    <div class="col-lg-7 col-xl-8">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-exclamation-circle me-2" style="color: var(--maroon);"></i>My Upcoming Deadlines
                </h6>
                <a href="<?php echo e(route('faculty.tasks.index')); ?>" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" style="color: var(--navy);">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4 py-3">Task</th>
                                <th class="py-3">Priority</th>
                                <th class="py-3">Assigned Date</th>
                                <th class="py-3">Deadline</th>
                                <th class="pe-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $upcomingDeadlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr onclick="window.location='<?php echo e(route('faculty.tasks.show', $task)); ?>'" style="cursor: pointer;">
                                <td class="ps-4">
                                    <a href="<?php echo e(route('faculty.tasks.show', $task)); ?>" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                        <?php echo e($task->title); ?>

                                    </a>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-<?php echo e($task->priority_color); ?> px-2 py-1">
                                        <?php echo e(ucfirst($task->priority)); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size:0.82rem;">
                                        <i class="bi bi-calendar-event me-1"></i><?php echo e($task->created_at->format('M d, Y')); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="<?php echo e($task->deadline->isPast() ? 'text-danger fw-semibold' : ($task->deadline->diffInDays(now()) < 3 ? 'text-warning fw-semibold' : 'text-muted')); ?>" style="font-size:0.82rem;">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo e($task->deadline->format('M d, Y H:i')); ?>

                                        <?php if($task->deadline->isPast()): ?>
                                            <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Overdue</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <a href="<?php echo e(route('faculty.tasks.show', $task)); ?>" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No upcoming deadlines! Great job!</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Meetings Row -->
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-calendar-event me-2" style="color: var(--navy);"></i>Upcoming Meetings
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingMeetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meeting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="col-md-4">
                            <a href="<?php echo e(route('faculty.meetings.show', $meeting)); ?>" class="p-3 border rounded bg-light d-block text-decoration-none" style="cursor: pointer;">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-semibold text-dark"><?php echo e($meeting->title); ?></h6>
                                    <span class="badge bg-white text-primary border" style="font-size: 0.7rem;">
                                        <?php echo e($meeting->scheduled_at->diffForHumans()); ?>

                                    </span>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i> <?php echo e($meeting->venue ?? 'TBA'); ?>

                                </small>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-12 text-center py-3 text-muted">
                            <i class="bi bi-calendar-x fs-3 d-block mb-1 text-secondary"></i>
                            <p class="mb-0 small">No upcoming meetings scheduled.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/faculty/dashboard.blade.php ENDPATH**/ ?>