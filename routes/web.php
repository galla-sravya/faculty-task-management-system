<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
       return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Test route to preview the email in the browser
Route::get('/test-email', function () {
    $task = \App\Models\Task::first();
    return new \App\Mail\TaskAssigned($task);
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Role-based dashboard redirect
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->isHod()) {
            return redirect()->route('hod.dashboard');
        }
        if (auth()->user()->isNbaCoordinator()) {
            return redirect()->route('nba.dashboard');
        }
        if (auth()->user()->isCoordinator()) {
            return redirect()->route('coordinator.dashboard');
        }
        return redirect()->route('faculty.dashboard');
    })->name('dashboard');
    
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markRead');

    // Global Search & Calendar
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('global.search');
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');

    // Workload Check
    Route::post('/tasks/check-workload', [App\Http\Controllers\WorkloadController::class, 'check'])->name('tasks.checkWorkload');

    // Smart task redirect — works for any role (used in email links)
    Route::get('/tasks/{task}/view', function (App\Models\Task $task) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isHod()) {
            return redirect()->route('hod.tasks.show', $task);
        }
        if ($user->isNbaCoordinator()) {
            return redirect()->route('nba.tasks.show', $task);
        }
        if ($user->isCoordinator()) {
            return redirect()->route('coordinator.tasks.show', $task);
        }
        return redirect()->route('faculty.tasks.show', $task);
    })->name('tasks.view')->withTrashed();

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // HOD Accounts
        Route::resource('hods', App\Http\Controllers\Admin\HodController::class)->except(['create', 'edit', 'show']);
        Route::post('hods/{hod}/toggle-status', [App\Http\Controllers\Admin\HodController::class, 'toggleStatus'])->name('hods.toggleStatus');
        Route::post('hods/{hod}/reset-password', [App\Http\Controllers\Admin\HodController::class, 'resetPassword'])->name('hods.resetPassword');

        // NBA Coordinator Accounts
        Route::resource('nba-coordinators', App\Http\Controllers\Admin\NbaCoordinatorController::class)->except(['create', 'edit', 'show']);
        Route::post('nba-coordinators/{nba_coordinator}/toggle-status', [App\Http\Controllers\Admin\NbaCoordinatorController::class, 'toggleStatus'])->name('nba-coordinators.toggleStatus');
        Route::post('nba-coordinators/{nba_coordinator}/reset-password', [App\Http\Controllers\Admin\NbaCoordinatorController::class, 'resetPassword'])->name('nba-coordinators.resetPassword');

        // Dynamic Coordinator Accounts
        Route::resource('dynamic-coordinators', App\Http\Controllers\Admin\DynamicCoordinatorController::class)->except(['create', 'edit', 'show']);
        Route::post('dynamic-coordinators/{dynamic_coordinator}/toggle-status', [App\Http\Controllers\Admin\DynamicCoordinatorController::class, 'toggleStatus'])->name('dynamic-coordinators.toggleStatus');
        Route::post('dynamic-coordinators/{dynamic_coordinator}/reset-password', [App\Http\Controllers\Admin\DynamicCoordinatorController::class, 'resetPassword'])->name('dynamic-coordinators.resetPassword');

        // Departments & Faculty inside Department
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
        Route::post('departments/{department}/toggle-status', [App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('departments.toggleStatus');

        Route::post('departments/{department}/faculty', [App\Http\Controllers\Admin\DepartmentFacultyController::class, 'store'])->name('departments.faculty.store');
        Route::put('departments/{department}/faculty/{faculty}', [App\Http\Controllers\Admin\DepartmentFacultyController::class, 'update'])->name('departments.faculty.update');
        Route::post('departments/{department}/faculty/{faculty}/toggle-status', [App\Http\Controllers\Admin\DepartmentFacultyController::class, 'toggleStatus'])->name('departments.faculty.toggleStatus');
        Route::post('departments/{department}/faculty/{faculty}/reset-password', [App\Http\Controllers\Admin\DepartmentFacultyController::class, 'resetPassword'])->name('departments.faculty.resetPassword');
        Route::delete('departments/{department}/faculty/{faculty}', [App\Http\Controllers\Admin\DepartmentFacultyController::class, 'destroy'])->name('departments.faculty.destroy');

        // Dynamic Coordinator Types
        Route::resource('coordinator-types', App\Http\Controllers\Admin\CoordinatorTypeController::class)->except(['create', 'edit', 'show']);
        Route::post('coordinator-types/{coordinator_type}/toggle-status', [App\Http\Controllers\Admin\CoordinatorTypeController::class, 'toggleStatus'])->name('coordinator-types.toggleStatus');
    });

    // Generic Coordinator Routes
    Route::middleware('role:coordinator')->prefix('coordinator')->name('coordinator.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Coordinator\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/charts', [App\Http\Controllers\Coordinator\DashboardController::class, 'chartsData'])->name('api.charts');
        Route::get('/api/dashboard-filter', [App\Http\Controllers\Coordinator\DashboardController::class, 'filterData'])->name('api.filter');
        
        // Archived tasks & restore
        Route::get('tasks/archived', [App\Http\Controllers\Coordinator\TaskController::class, 'archived'])->name('tasks.archived');
        Route::post('tasks/{task}/restore', [App\Http\Controllers\Coordinator\TaskController::class, 'restore'])->name('tasks.restore');

        Route::resource('tasks', App\Http\Controllers\Coordinator\TaskController::class)->withTrashed(['show']);

        Route::get('reports', [App\Http\Controllers\Coordinator\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [App\Http\Controllers\Coordinator\ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/{task}', [App\Http\Controllers\Coordinator\ReportController::class, 'show'])->name('reports.show');

        // Document review routes
        Route::post('tasks/{task}/documents/{document}/review', [App\Http\Controllers\Coordinator\TaskDocumentController::class, 'review'])->name('tasks.documents.review')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\Coordinator\TaskDocumentController::class, 'download'])->name('tasks.documents.download')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\Coordinator\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions')->withTrashed();

        // Coordinator Faculty Management
        Route::get('faculty', [App\Http\Controllers\Coordinator\FacultyController::class, 'index'])->name('faculty.index');
        Route::get('faculty/{faculty}/performance', [App\Http\Controllers\Coordinator\FacultyController::class, 'performance'])->name('faculty.performance');
    });

    // NBA Coordinator Routes
    Route::middleware('role:nba_coordinator')->prefix('nba')->name('nba.')->group(function () {
        Route::get('/api/charts', [App\Http\Controllers\NBA\DashboardController::class, 'getChartsData'])->name('api.charts');
        Route::get('/api/filter-tasks', [App\Http\Controllers\NBA\DashboardController::class, 'filterTasks'])->name('api.filterTasks');

        Route::get('/dashboard', [App\Http\Controllers\NBA\DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('tasks/archived', [App\Http\Controllers\NBA\TaskController::class, 'archived'])->name('tasks.archived');
        Route::post('tasks/{task}/restore', [App\Http\Controllers\NBA\TaskController::class, 'restore'])->name('tasks.restore');
        Route::resource('tasks', App\Http\Controllers\NBA\TaskController::class)->withTrashed(['show']);

        Route::resource('meetings', App\Http\Controllers\NBA\MeetingController::class);
        Route::get('meetings/{meeting}/complete', [App\Http\Controllers\NBA\MeetingController::class, 'completeForm'])->name('meetings.completeForm');
        Route::post('meetings/{meeting}/complete', [App\Http\Controllers\NBA\MeetingController::class, 'complete'])->name('meetings.complete');

        Route::get('reports', [App\Http\Controllers\NBA\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [App\Http\Controllers\NBA\ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/{task}', [App\Http\Controllers\NBA\ReportController::class, 'show'])->name('reports.show');

        Route::post('tasks/{task}/documents/{document}/review', [App\Http\Controllers\NBA\TaskDocumentController::class, 'review'])->name('tasks.documents.review')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\NBA\TaskDocumentController::class, 'download'])->name('tasks.documents.download')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\NBA\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions')->withTrashed();

        // NBA Faculty Management
        Route::get('faculty', [App\Http\Controllers\NBA\FacultyController::class, 'index'])->name('faculty.index');
        Route::get('faculty/{faculty}/performance', [App\Http\Controllers\NBA\FacultyController::class, 'performance'])->name('faculty.performance');
    });

    // HOD Routes
    Route::middleware('role:hod')->prefix('hod')->name('hod.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\HOD\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/charts', [App\Http\Controllers\HOD\DashboardController::class, 'chartsData'])->name('api.charts');
        Route::get('/api/dashboard-filter', [App\Http\Controllers\HOD\DashboardController::class, 'filterData'])->name('api.filter');
        
        // Archived tasks & restore
        Route::get('tasks/archived', [App\Http\Controllers\HOD\TaskController::class, 'archived'])->name('tasks.archived');
        Route::post('tasks/{task}/restore', [App\Http\Controllers\HOD\TaskController::class, 'restore'])->name('tasks.restore');

        Route::resource('tasks', App\Http\Controllers\HOD\TaskController::class)->withTrashed(['show']);
        
        Route::resource('meetings', App\Http\Controllers\HOD\MeetingController::class);
        Route::get('meetings/{meeting}/complete', [App\Http\Controllers\HOD\MeetingController::class, 'completeForm'])->name('meetings.completeForm');
        Route::post('meetings/{meeting}/complete', [App\Http\Controllers\HOD\MeetingController::class, 'complete'])->name('meetings.complete');
        
        Route::resource('faculty', App\Http\Controllers\HOD\FacultyController::class)->except(['edit', 'update', 'destroy', 'show']);
        Route::get('faculty/{faculty}/performance', [App\Http\Controllers\HOD\FacultyController::class, 'performance'])->name('faculty.performance');
        
        Route::get('reports', [App\Http\Controllers\HOD\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [App\Http\Controllers\HOD\ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/{task}', [App\Http\Controllers\HOD\ReportController::class, 'show'])->name('reports.show');

        // Document review routes
        Route::post('tasks/{task}/documents/{document}/review', [App\Http\Controllers\HOD\TaskDocumentController::class, 'review'])->name('tasks.documents.review')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\HOD\TaskDocumentController::class, 'download'])->name('tasks.documents.download')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\HOD\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions')->withTrashed();
    });
    
    // Faculty Routes
    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Faculty\DashboardController::class, 'index'])->name('dashboard');
        
        // Archived tasks & restore
        Route::get('tasks/archived', [App\Http\Controllers\Faculty\TaskController::class, 'archived'])->name('tasks.archived');
        Route::post('tasks/{task}/restore', [App\Http\Controllers\Faculty\TaskController::class, 'restore'])->name('tasks.restore');

        Route::post('tasks/{task}/progress', [App\Http\Controllers\Faculty\TaskController::class, 'updateProgress'])->name('tasks.updateProgress')->withTrashed();
        Route::resource('tasks', App\Http\Controllers\Faculty\TaskController::class)->withTrashed(['show']);

        Route::get('meetings', [App\Http\Controllers\Faculty\MeetingController::class, 'index'])->name('meetings.index');
        Route::get('meetings/{meeting}', [App\Http\Controllers\Faculty\MeetingController::class, 'show'])->name('meetings.show');
        
        Route::get('reports', [App\Http\Controllers\Faculty\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{task}', [App\Http\Controllers\Faculty\ReportController::class, 'show'])->name('reports.show');

        // Document routes
        Route::post('tasks/{task}/documents', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'store'])->name('tasks.documents.store')->withTrashed();
        Route::post('tasks/{task}/documents/{document}/replace', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'replace'])->name('tasks.documents.replace')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'download'])->name('tasks.documents.download')->withTrashed();
        Route::post('tasks/{task}/documents/submit', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'submit'])->name('tasks.documents.submit')->withTrashed();
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions')->withTrashed();
        Route::post('tasks/{task}/documents/{document}/review', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'review'])->name('tasks.documents.review')->withTrashed();
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Task Collaborator Routes
    Route::post('/tasks/{task}/collaborators', [App\Http\Controllers\TaskCollaboratorController::class, 'store'])->name('tasks.collaborators.store')->withTrashed();
    Route::delete('/tasks/{task}/collaborators/{user}', [App\Http\Controllers\TaskCollaboratorController::class, 'destroy'])->name('tasks.collaborators.destroy')->withTrashed();
        
    // Task Reminder & Completion Routes
    Route::post('/tasks/{task}/send-reminders', [App\Http\Controllers\TaskReminderController::class, 'sendReminder'])->name('tasks.sendReminder');
    Route::post('/tasks/{task}/complete', [App\Http\Controllers\TaskReminderController::class, 'markAsCompleted'])->name('tasks.complete');
    Route::post('/nba-tasks/{task}/send-reminders', [App\Http\Controllers\TaskReminderController::class, 'sendNbaReminder'])->name('nba.tasks.sendReminder');

    // Task Assignment Routes
    Route::post('/tasks/{task}/assign', [App\Http\Controllers\TaskCollaboratorController::class, 'assign'])->name('tasks.assign');

    // Task Reassignment Route
    Route::post('/tasks/{task}/reassign', [App\Http\Controllers\TaskCollaboratorController::class, 'reassign'])->name('tasks.reassign');

    // Task Comments Routes
    Route::post('/tasks/{task}/comments', [App\Http\Controllers\TaskCommentController::class, 'store'])->name('tasks.comments.store')->withTrashed();

    // Workload Check
    Route::post('/tasks/workload-check', [App\Http\Controllers\WorkloadCheckController::class, 'check'])->name('tasks.workload-check');

    // Task Checklist / Subtasks Routes
    Route::post('/tasks/{task}/checklist', [App\Http\Controllers\TaskChecklistController::class, 'store'])->name('tasks.checklist.store')->withTrashed();
    Route::put('/tasks/{task}/checklist/{item}', [App\Http\Controllers\TaskChecklistController::class, 'update'])->name('tasks.checklist.update')->withTrashed();
    Route::patch('/tasks/{task}/checklist/{item}/status', [App\Http\Controllers\TaskChecklistController::class, 'updateStatus'])->name('tasks.checklist.updateStatus')->withTrashed();
    Route::post('/tasks/{task}/checklist/{item}/toggle', [App\Http\Controllers\TaskChecklistController::class, 'toggle'])->name('tasks.checklist.toggle')->withTrashed();
    Route::delete('/tasks/{task}/checklist/{item}', [App\Http\Controllers\TaskChecklistController::class, 'destroy'])->name('tasks.checklist.destroy')->withTrashed();
});

// Google Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('google.login');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('google.callback');
});

require __DIR__.'/auth.php';
