<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacultyWorkflowStagesTest extends TestCase
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
            'email'         => 'hod_stage@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'hod',
            'department_id' => $dept->id,
        ]);

        $this->faculty = User::create([
            'name'          => 'Dr. Faculty Member',
            'email'         => 'faculty_stage@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'faculty',
            'department_id' => $dept->id,
        ]);

        $this->task = Task::create([
            'title'         => 'Faculty Workflow Stage Task',
            'description'   => 'Testing new faculty workflow stages',
            'created_by'    => $this->hod->id,
            'owner_role'    => 'hod',
            'department_id' => $dept->id,
            'priority'      => 'high',
            'status'        => 'not_started',
            'deadline'      => now()->addDays(5),
        ]);

        $this->task->assignees()->attach($this->faculty->id, [
            'status'              => 'not_started',
            'progress_percentage' => 0,
            'role'                => 'owner',
        ]);
    }

    public function test_faculty_task_page_does_not_contain_completed_option()
    {
        $response = $this->actingAs($this->faculty)->get(route('faculty.tasks.show', $this->task));
        $response->assertOk();

        // Verify clean text options without emojis
        $response->assertSee('>Not Started</option>', false);
        $response->assertSee('>Collecting Resources</option>', false);
        $response->assertSee('>Working on Task</option>', false);
        $response->assertSee('>Supporting Documents Uploaded</option>', false);
        $response->assertSee('>Checklist Completed</option>', false);
        $response->assertSee('>Submitted for Review</option>', false);

        // Verify "completed" option is NOT in the status dropdown
        $response->assertDontSee('<option value="completed"', false);
    }

    public function test_faculty_cannot_manually_submit_completed_status()
    {
        $response = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'completed',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_stage_not_started_sets_zero_progress()
    {
        $res = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'not_started',
        ]);
        $res->assertSessionHasNoErrors();
        $task = Task::find($this->task->id);
        $this->assertEquals(0, $task->overall_progress);
    }

    public function test_stage_collecting_resources_sets_fifteen_percent_progress()
    {
        $res = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'collecting_resources',
        ]);
        $res->assertSessionHasNoErrors();
        $task = Task::find($this->task->id);
        $this->assertEquals(15, $task->overall_progress);
    }

    public function test_stage_working_on_task_sets_thirty_five_percent_progress()
    {
        $res = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'working_on_task',
        ]);
        $res->assertSessionHasNoErrors();
        $task = Task::find($this->task->id);
        $this->assertEquals(35, $task->overall_progress);
    }

    public function test_stage_documents_uploaded_sets_fifty_five_percent_progress()
    {
        $res = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'documents_uploaded',
        ]);
        $res->assertSessionHasNoErrors();
        $task = Task::find($this->task->id);
        $this->assertEquals(55, $task->overall_progress);
    }

    public function test_stage_checklist_completed_sets_seventy_five_percent_progress()
    {
        $res = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'checklist_completed',
        ]);
        $res->assertSessionHasNoErrors();
        $task = Task::find($this->task->id);
        $this->assertEquals(75, $task->overall_progress);
    }

    public function test_stage_submitted_for_review_sets_ninety_percent_progress()
    {
        $res = $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'submitted_for_review',
        ]);
        $res->assertSessionHasNoErrors();
        $task = Task::find($this->task->id);
        $this->assertEquals(90, $task->overall_progress);
    }

    public function test_submitting_for_review_creates_notification_timeline_and_audit()
    {
        $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status'  => 'submitted_for_review',
            'remarks' => 'Ready for HOD evaluation',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->hod->id,
            'type'    => 'progress_updated',
        ]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'action'  => 'progress_updated',
        ]);

        $this->assertDatabaseHas('task_audit_logs', [
            'task_id'    => $this->task->id,
            'field_name' => 'progress_percentage',
        ]);
    }

    public function test_hod_approval_and_request_changes_workflow()
    {
        // Faculty submits for review
        $this->actingAs($this->faculty)->post(route('faculty.tasks.updateProgress', $this->task), [
            'status' => 'submitted_for_review',
        ]);

        $file = UploadedFile::fake()->create('Final_Submission.pdf', 100, 'application/pdf');
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);
        $doc = TaskDocument::where('file_name', 'Final_Submission.pdf')->first();

        // HOD requests changes
        $this->actingAs($this->hod)->post(route('hod.tasks.documents.review', [$this->task, $doc]), [
            'action'          => 'changes_requested',
            'review_comments' => 'Please revise section 2',
        ]);

        $this->task->refresh();
        $this->assertEquals('working_on_task', $this->task->status);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->faculty->id,
            'type'    => 'document_changes_requested',
        ]);

        // HOD approves document
        $this->actingAs($this->hod)->post(route('hod.tasks.documents.review', [$this->task, $doc]), [
            'action' => 'approved',
        ]);

        $this->task->refresh();
        $this->task->load('assignees');

        $this->assertEquals(100, $this->task->overall_progress);
        $this->assertEquals('completed', $this->task->status);
    }
}
