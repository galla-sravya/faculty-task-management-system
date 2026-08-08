<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Test route to preview the email in the browser
Route::get('/test-email', function () {
    $task = \App\Models\Task::first();
    return new \App\Mail\TaskAssigned($task);
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Role-based dashboard redirect
    Route::get('/dashboard', function () {
        if (auth()->user()->isHod()) {
            return redirect()->route('hod.dashboard');
        }
        if (auth()->user()->isNbaCoordinator()) {
            return redirect()->route('nba.dashboard');
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

    // Smart task redirect — works for any role (used in email links)
    Route::get('/tasks/{task}/view', function (App\Models\Task $task) {
        $user = auth()->user();
        if ($user->isHod()) {
            return redirect()->route('hod.tasks.show', $task);
        }
        if ($user->isNbaCoordinator()) {
            return redirect()->route('nba.tasks.show', $task);
        }
        return redirect()->route('faculty.tasks.show', $task);
    })->name('tasks.view');

    // NBA Coordinator Routes
    Route::middleware('role:nba_coordinator')->prefix('nba')->name('nba.')->group(function () {
        Route::get('/api/charts', [App\Http\Controllers\NBA\DashboardController::class, 'getChartsData'])->name('api.charts');
        Route::get('/api/filter-tasks', [App\Http\Controllers\NBA\DashboardController::class, 'filterTasks'])->name('api.filterTasks');

        Route::get('/dashboard', [App\Http\Controllers\NBA\DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('tasks/archived', [App\Http\Controllers\NBA\TaskController::class, 'archived'])->name('tasks.archived');
        Route::post('tasks/{task}/restore', [App\Http\Controllers\NBA\TaskController::class, 'restore'])->name('tasks.restore');
        Route::resource('tasks', App\Http\Controllers\NBA\TaskController::class);

        Route::resource('meetings', App\Http\Controllers\NBA\MeetingController::class);
        Route::get('meetings/{meeting}/complete', [App\Http\Controllers\NBA\MeetingController::class, 'completeForm'])->name('meetings.completeForm');
        Route::post('meetings/{meeting}/complete', [App\Http\Controllers\NBA\MeetingController::class, 'complete'])->name('meetings.complete');

        Route::get('reports', [App\Http\Controllers\NBA\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [App\Http\Controllers\NBA\ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/{task}', [App\Http\Controllers\NBA\ReportController::class, 'show'])->name('reports.show');

        Route::post('tasks/{task}/documents/{document}/review', [App\Http\Controllers\NBA\TaskDocumentController::class, 'review'])->name('tasks.documents.review');
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\NBA\TaskDocumentController::class, 'download'])->name('tasks.documents.download');
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\NBA\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions');

        // NBA Faculty Management (view-only — no create/store)
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

        Route::resource('tasks', App\Http\Controllers\HOD\TaskController::class);
        
        Route::resource('meetings', App\Http\Controllers\HOD\MeetingController::class);
        Route::get('meetings/{meeting}/complete', [App\Http\Controllers\HOD\MeetingController::class, 'completeForm'])->name('meetings.completeForm');
        Route::post('meetings/{meeting}/complete', [App\Http\Controllers\HOD\MeetingController::class, 'complete'])->name('meetings.complete');
        
        Route::resource('faculty', App\Http\Controllers\HOD\FacultyController::class)->except(['edit', 'update', 'destroy', 'show']);
        Route::get('faculty/{faculty}/performance', [App\Http\Controllers\HOD\FacultyController::class, 'performance'])->name('faculty.performance');
        
        Route::get('reports', [App\Http\Controllers\HOD\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [App\Http\Controllers\HOD\ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/{task}', [App\Http\Controllers\HOD\ReportController::class, 'show'])->name('reports.show');

        // Document review routes
        Route::post('tasks/{task}/documents/{document}/review', [App\Http\Controllers\HOD\TaskDocumentController::class, 'review'])->name('tasks.documents.review');
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\HOD\TaskDocumentController::class, 'download'])->name('tasks.documents.download');
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\HOD\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions');
    });
    
    // Faculty Routes
    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Faculty\DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('tasks', [App\Http\Controllers\Faculty\TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/{task}', [App\Http\Controllers\Faculty\TaskController::class, 'show'])->name('tasks.show');
        Route::post('tasks/{task}/progress', [App\Http\Controllers\Faculty\TaskController::class, 'updateProgress'])->name('tasks.updateProgress');

        Route::get('meetings', [App\Http\Controllers\Faculty\MeetingController::class, 'index'])->name('meetings.index');
        Route::get('meetings/{meeting}', [App\Http\Controllers\Faculty\MeetingController::class, 'show'])->name('meetings.show');
        
        Route::get('reports', [App\Http\Controllers\Faculty\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{task}', [App\Http\Controllers\Faculty\ReportController::class, 'show'])->name('reports.show');

        // Document routes
        Route::post('tasks/{task}/documents', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'store'])->name('tasks.documents.store');
        Route::post('tasks/{task}/documents/{document}/replace', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'replace'])->name('tasks.documents.replace');
        Route::get('tasks/{task}/documents/{document}/download', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'download'])->name('tasks.documents.download');
        Route::post('tasks/{task}/documents/submit', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'submit'])->name('tasks.documents.submit');
        Route::get('tasks/{task}/documents/{document}/versions', [App\Http\Controllers\Faculty\TaskDocumentController::class, 'versions'])->name('tasks.documents.versions');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Task Collaborator Routes
    Route::post('/tasks/{task}/collaborators', [App\Http\Controllers\TaskCollaboratorController::class, 'store'])->name('tasks.collaborators.store');
    Route::delete('/tasks/{task}/collaborators/{user}', [App\Http\Controllers\TaskCollaboratorController::class, 'destroy'])->name('tasks.collaborators.destroy');

    // Task Reassignment Route
    Route::post('/tasks/{task}/reassign', [App\Http\Controllers\TaskCollaboratorController::class, 'reassign'])->name('tasks.reassign');

    // Task Comments Routes
    Route::post('/tasks/{task}/comments', [App\Http\Controllers\TaskCommentController::class, 'store'])->name('tasks.comments.store');

    // Task Checklist / Subtasks Routes
    Route::post('/tasks/{task}/checklist', [App\Http\Controllers\TaskChecklistController::class, 'store'])->name('tasks.checklist.store');
    Route::post('/tasks/{task}/checklist/{item}/toggle', [App\Http\Controllers\TaskChecklistController::class, 'toggle'])->name('tasks.checklist.toggle');
    Route::delete('/tasks/{task}/checklist/{item}', [App\Http\Controllers\TaskChecklistController::class, 'destroy'])->name('tasks.checklist.destroy');
});

require __DIR__.'/auth.php';
