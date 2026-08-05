<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\TaskAuditLog;
use App\Models\TaskActivity;
use App\Models\TaskChecklistItem;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseTaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $hod;
    protected User $faculty;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create(['name' => 'Computer Science', 'code' => 'CS']);

        $this->hod = User::create([
            'name' => 'Dr. HOD',
            'email' => 'hod@example.com',
            'password' => bcrypt('password'),
            'role' => 'hod',
            'department_id' => $department->id,
        ]);

        $this->faculty = User::create([
            'name' => 'Prof. Faculty',
            'email' => 'faculty@example.com',
            'password' => bcrypt('password'),
            'role' => 'faculty',
            'department_id' => $department->id,
        ]);

        $this->task = Task::create([
            'title' => 'Initial Task Title',
            'description' => 'Original Description',
            'priority' => 'medium',
            'category' => 'Academics',
            'deadline' => now()->addDays(5),
            'created_by' => $this->hod->id,
            'department_id' => $department->id,
            'status' => 'pending',
        ]);

        $this->task->assignees()->attach($this->faculty->id, [
            'status' => 'pending',
            'progress_percentage' => 0,
            'role' => 'owner',
            'assigned_by' => $this->hod->id,
            'assigned_at' => now(),
        ]);
    }

    public function test_hod_editing_task_generates_audit_logs_for_changed_fields_only()
    {
        $response = $this->actingAs($this->hod)->put(route('hod.tasks.update', $this->task), [
            'title'       => 'Updated Task Title',
            'description' => 'Original Description', // Unchanged
            'category'    => 'Academics',             // Unchanged
            'priority'    => 'high',                  // Changed
            'deadline'    => now()->addDays(5)->format('Y-m-d H:i'), // Unchanged
            'assignees'   => [$this->faculty->id],
        ]);

        $response->assertRedirect(route('hod.tasks.show', $this->task));

        $this->assertDatabaseHas('task_audit_logs', [
            'task_id'    => $this->task->id,
            'field_name' => 'title',
            'old_value'  => 'Initial Task Title',
            'new_value'  => 'Updated Task Title',
        ]);

        $this->assertDatabaseHas('task_audit_logs', [
            'task_id'    => $this->task->id,
            'field_name' => 'priority',
            'old_value'  => 'Medium',
            'new_value'  => 'High',
        ]);

        // Unchanged description should not generate an audit log entry
        $this->assertDatabaseMissing('task_audit_logs', [
            'task_id'    => $this->task->id,
            'field_name' => 'description',
        ]);
    }

    public function test_task_archiving_and_restoration()
    {
        // Archive (Soft delete)
        $response = $this->actingAs($this->hod)->delete(route('hod.tasks.destroy', $this->task));
        $response->assertRedirect(route('hod.tasks.index'));

        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'action'  => 'archived',
        ]);

        $this->assertDatabaseHas('task_audit_logs', [
            'task_id'    => $this->task->id,
            'field_name' => 'task_archived',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->faculty->id,
            'type'    => 'task_archived',
        ]);

        // Test Archived Tasks Page Search & View
        $archivedPage = $this->actingAs($this->hod)->get(route('hod.tasks.archived', ['search' => 'Initial Task']));
        $archivedPage->assertStatus(200);
        $archivedPage->assertSee('Initial Task Title');

        // Restore
        $restoreResponse = $this->actingAs($this->hod)->post(route('hod.tasks.restore', $this->task->id));
        $restoreResponse->assertRedirect(route('hod.tasks.show', $this->task));

        $this->assertDatabaseHas('tasks', [
            'id'         => $this->task->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'action'  => 'restored',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->faculty->id,
            'type'    => 'task_restored',
        ]);
    }

    public function test_subtask_checklist_and_automatic_progress_calculation()
    {
        $item1 = TaskChecklistItem::create(['task_id' => $this->task->id, 'title' => 'Subtask 1']);
        $item2 = TaskChecklistItem::create(['task_id' => $this->task->id, 'title' => 'Subtask 2']);

        // Toggle item1 completed
        $this->actingAs($this->faculty)->post(route('tasks.checklist.toggle', [$this->task, $item1]));

        $this->assertDatabaseHas('task_checklist_items', [
            'id'           => $item1->id,
            'is_completed' => true,
        ]);

        // 1 of 2 completed = 50%
        $this->assertDatabaseHas('task_user', [
            'task_id'             => $this->task->id,
            'user_id'             => $this->faculty->id,
            'progress_percentage' => 50,
        ]);
    }

    public function test_task_comments_and_discussion()
    {
        $response = $this->actingAs($this->faculty)->post(route('tasks.comments.store', $this->task), [
            'comment' => 'Progress is on track for chapter 1.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->faculty->id,
            'comment' => 'Progress is on track for chapter 1.',
        ]);
    }

    public function test_global_search_and_csv_reports_export()
    {
        // Global search
        $searchResponse = $this->actingAs($this->hod)->get(route('global.search', ['q' => 'Initial Task']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Initial Task Title');

        // CSV export
        $csvResponse = $this->actingAs($this->hod)->get(route('hod.reports.export', ['type' => 'tasks']));
        $csvResponse->assertStatus(200);
        $csvResponse->assertHeader('Content-type', 'text/csv; charset=utf-8');
    }
}
