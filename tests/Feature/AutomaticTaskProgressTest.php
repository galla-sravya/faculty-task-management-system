<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskChecklistItem;
use App\Models\TaskDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutomaticTaskProgressTest extends TestCase
{
    use RefreshDatabase;

    protected User $hod;
    protected User $faculty;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $dept = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);

        $this->hod = User::create([
            'name'          => 'Dr. HOD',
            'email'         => 'hod_auto@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'hod',
            'department_id' => $dept->id,
        ]);

        $this->faculty = User::create([
            'name'          => 'Dr. Faculty Member',
            'email'         => 'faculty_auto@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'faculty',
            'department_id' => $dept->id,
        ]);

        $this->task = Task::create([
            'title'         => 'Automated Progress Calculation Task',
            'description'   => 'Testing automated progress milestone rules',
            'created_by'    => $this->hod->id,
            'owner_role'    => 'hod',
            'department_id' => $dept->id,
            'priority'      => 'high',
            'status'        => 'pending',
            'deadline'      => now()->addDays(5),
        ]);

        $this->task->assignees()->attach($this->faculty->id, [
            'status'              => 'pending',
            'progress_percentage' => 0,
            'role'                => 'owner',
        ]);
    }

    public function test_initial_assigned_task_has_zero_progress()
    {
        $this->assertEquals(0, $this->task->overall_progress);
    }

    public function test_changing_status_to_in_progress_sets_ten_percent()
    {
        $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status'  => 'in_progress',
            'remarks' => 'Starting work',
        ]);

        $this->task->refresh();
        $this->assertEquals(10, $this->task->overall_progress);
        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'action'  => 'progress_updated',
        ]);
    }

    public function test_first_document_upload_sets_thirty_percent_when_checklist_exists()
    {
        // Change status to in_progress
        $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'in_progress',
        ]);

        // Add 2 checklist items (uncompleted)
        TaskChecklistItem::create(['task_id' => $this->task->id, 'title' => 'Subtask 1']);
        TaskChecklistItem::create(['task_id' => $this->task->id, 'title' => 'Subtask 2']);

        // Upload first document -> 30%
        $file = UploadedFile::fake()->create('Draft.pdf', 100, 'application/pdf');
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);

        $this->task->refresh();
        $this->assertEquals(30, $this->task->overall_progress);

        // Re-uploading a document should not repeatedly increase progress
        $file2 = UploadedFile::fake()->create('Draft_v2.pdf', 100, 'application/pdf');
        $doc = TaskDocument::where('file_name', 'Draft.pdf')->first();
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.replace', [$this->task, $doc]), [
            'document' => $file2,
        ]);

        $this->task->refresh();
        $this->assertEquals(30, $this->task->overall_progress);
    }

    public function test_checklist_items_proportionally_increase_progress_up_to_70_percent()
    {
        // Add 2 checklist items
        $item1 = TaskChecklistItem::create(['task_id' => $this->task->id, 'title' => 'Subtask 1']);
        $item2 = TaskChecklistItem::create(['task_id' => $this->task->id, 'title' => 'Subtask 2']);

        // Upload first document
        $file = UploadedFile::fake()->create('Evidence.pdf', 100, 'application/pdf');
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);

        // 1 of 2 completed -> 30 + (1/2 * 40) = 50%
        $this->actingAs($this->faculty)->post(route('tasks.checklist.toggle', [$this->task, $item1]));
        $this->task = $this->task->fresh(['assignees']);
        $this->assertEquals(50, $this->task->overall_progress);

        // 2 of 2 completed -> 30 + (2/2 * 40) = 70%
        $this->actingAs($this->faculty)->post(route('tasks.checklist.toggle', [$this->task, $item2]));
        $this->task = $this->task->fresh(['assignees']);
        $this->assertEquals(70, $this->task->overall_progress);
    }

    public function test_submitting_for_review_sets_ninety_percent()
    {
        // Upload document
        $file = UploadedFile::fake()->create('Final_Report.pdf', 100, 'application/pdf');
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);

        // Submit for review
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.submit', $this->task));
        $this->task->refresh();

        $this->assertEquals(90, $this->task->overall_progress);
        $this->assertEquals('pending_review', $this->task->status);
    }

    public function test_hod_approval_sets_one_hundred_percent()
    {
        $file = UploadedFile::fake()->create('Final_Report.pdf', 100, 'application/pdf');
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.submit', $this->task));
        $doc = TaskDocument::where('file_name', 'Final_Report.pdf')->first();

        // HOD approves document
        $this->actingAs($this->hod)->post(route('hod.tasks.documents.review', [$this->task, $doc]), [
            'action' => 'approved',
        ]);

        // Faculty marks task completed
        $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'completed',
        ]);

        $this->task->refresh();
        $this->task->load('assignees');
        $this->assertEquals(100, $this->task->overall_progress);
        $this->assertEquals('completed', $this->task->status);
    }
}
