<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\TaskDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskDocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $hod;
    protected User $faculty;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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
            'title' => 'Prepare Syllabus Document',
            'description' => 'Upload syllabus draft',
            'priority' => 'high',
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

    public function test_faculty_can_upload_document_as_draft()
    {
        $file = UploadedFile::fake()->create('syllabus.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
            'remarks'   => 'First draft',
        ]);

        $response->assertRedirect(route('faculty.tasks.show', $this->task));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('task_documents', [
            'task_id'       => $this->task->id,
            'user_id'       => $this->faculty->id,
            'file_name'     => 'syllabus.pdf',
            'version'       => 1,
            'review_status' => 'draft',
            'remarks'       => 'First draft',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->hod->id,
            'type'    => 'document_uploaded',
        ]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'action'  => 'document_uploaded',
        ]);
    }

    public function test_faculty_can_replace_document_creating_new_version()
    {
        $file1 = UploadedFile::fake()->create('syllabus_v1.pdf', 500, 'application/pdf');

        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file1],
        ]);

        $doc1 = TaskDocument::where('file_name', 'syllabus_v1.pdf')->first();

        $file2 = UploadedFile::fake()->create('syllabus_v2.pdf', 600, 'application/pdf');

        $response = $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.replace', [$this->task, $doc1]), [
            'document' => $file2,
            'remarks'  => 'Updated version with corrections',
        ]);

        $response->assertRedirect(route('faculty.tasks.show', $this->task));

        $this->assertDatabaseHas('task_documents', [
            'task_id'              => $this->task->id,
            'file_name'            => 'syllabus_v2.pdf',
            'version'              => 2,
            'original_document_id' => $doc1->id,
            'review_status'        => 'draft',
        ]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'action'  => 'document_replaced',
        ]);
    }

    public function test_faculty_can_submit_documents_for_review()
    {
        $file = UploadedFile::fake()->create('report.pdf', 300, 'application/pdf');

        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);

        $response = $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.submit', $this->task));

        $response->assertRedirect(route('faculty.tasks.show', $this->task));

        $this->assertDatabaseHas('task_documents', [
            'task_id'       => $this->task->id,
            'review_status' => 'submitted',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id'     => $this->task->id,
            'status' => 'pending_review',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->hod->id,
            'type'    => 'document_submitted',
        ]);
    }

    public function test_hod_can_approve_submitted_document()
    {
        $file = UploadedFile::fake()->create('report.pdf', 300, 'application/pdf');

        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.submit', $this->task));

        $doc = TaskDocument::first();

        $response = $this->actingAs($this->hod)->post(route('hod.tasks.documents.review', [$this->task, $doc]), [
            'action'          => 'approved',
            'review_comments' => 'Looks great!',
        ]);

        $response->assertRedirect(route('hod.tasks.show', $this->task));

        $this->assertDatabaseHas('task_documents', [
            'id'              => $doc->id,
            'review_status'   => 'approved',
            'review_comments' => 'Looks great!',
            'reviewed_by'     => $this->hod->id,
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->faculty->id,
            'type'    => 'document_approved',
        ]);
    }

    public function test_hod_can_request_changes_for_submitted_document()
    {
        $file = UploadedFile::fake()->create('report.pdf', 300, 'application/pdf');

        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$file],
        ]);
        $this->actingAs($this->faculty)->post(route('faculty.tasks.documents.submit', $this->task));

        $doc = TaskDocument::first();

        $response = $this->actingAs($this->hod)->post(route('hod.tasks.documents.review', [$this->task, $doc]), [
            'action'          => 'changes_requested',
            'review_comments' => 'Please add chapter 3 details.',
        ]);

        $response->assertRedirect(route('hod.tasks.show', $this->task));

        $this->assertDatabaseHas('task_documents', [
            'id'            => $doc->id,
            'review_status' => 'changes_requested',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->faculty->id,
            'type'    => 'document_changes_requested',
        ]);
    }
}
