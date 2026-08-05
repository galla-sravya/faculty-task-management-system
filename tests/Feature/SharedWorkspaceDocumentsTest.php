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

class SharedWorkspaceDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $hod;
    protected User $facultyA;
    protected User $facultyB;
    protected User $unassignedFaculty;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $dept = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);

        $this->hod = User::create([
            'name'          => 'Dr. HOD',
            'email'         => 'hod_doc@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'hod',
            'department_id' => $dept->id,
        ]);

        $this->facultyA = User::create([
            'name'          => 'Dr. Faculty A',
            'email'         => 'facA@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'faculty',
            'department_id' => $dept->id,
        ]);

        $this->facultyB = User::create([
            'name'          => 'Dr. Faculty B',
            'email'         => 'facB@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'faculty',
            'department_id' => $dept->id,
        ]);

        $this->unassignedFaculty = User::create([
            'name'          => 'Dr. Unassigned',
            'email'         => 'unassigned@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'faculty',
            'department_id' => $dept->id,
        ]);

        $this->task = Task::create([
            'title'         => 'Shared Workspace Collaborative Task',
            'description'   => 'Testing document collaboration',
            'created_by'    => $this->hod->id,
            'owner_role'    => 'hod',
            'department_id' => $dept->id,
            'priority'      => 'high',
            'status'        => 'in_progress',
            'deadline'      => now()->addDays(5),
        ]);

        // Assign both Faculty A and Faculty B as collaborators
        $this->task->assignees()->attach([
            $this->facultyA->id => ['status' => 'in_progress', 'progress_percentage' => 20, 'role' => 'owner'],
            $this->facultyB->id => ['status' => 'in_progress', 'progress_percentage' => 10, 'role' => 'collaborator'],
        ]);
    }

    public function test_collaborators_can_see_and_download_each_others_documents()
    {
        // Faculty A uploads a document
        $fileA = UploadedFile::fake()->create('FacultyA_Report.pdf', 100, 'application/pdf');
        $this->actingAs($this->facultyA)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$fileA],
            'remarks'   => 'Initial draft by Faculty A',
        ]);

        $docA = TaskDocument::where('file_name', 'FacultyA_Report.pdf')->first();
        $this->assertNotNull($docA);

        // Faculty B views the task and sees Faculty A's document
        $responseB = $this->actingAs($this->facultyB)->get(route('faculty.tasks.show', $this->task));
        $responseB->assertOk();
        $responseB->assertSee('FacultyA_Report.pdf');
        $responseB->assertSee('Dr. Faculty A');

        // Faculty B downloads Faculty A's document -> Success
        $downloadResponse = $this->actingAs($this->facultyB)->get(route('faculty.tasks.documents.download', [$this->task, $docA]));
        $downloadResponse->assertOk();
    }

    public function test_unassigned_faculty_cannot_view_or_download_task_documents()
    {
        $fileA = UploadedFile::fake()->create('Confidential.pdf', 100, 'application/pdf');
        $this->actingAs($this->facultyA)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$fileA],
        ]);
        $docA = TaskDocument::where('file_name', 'Confidential.pdf')->first();

        // Unassigned faculty accessing task details -> 403
        $resTask = $this->actingAs($this->unassignedFaculty)->get(route('faculty.tasks.show', $this->task));
        $resTask->assertStatus(403);

        // Unassigned faculty attempting download -> 403
        $resDownload = $this->actingAs($this->unassignedFaculty)->get(route('faculty.tasks.documents.download', [$this->task, $docA]));
        $resDownload->assertStatus(403);
    }

    public function test_only_document_owner_can_replace_their_own_document()
    {
        $fileA = UploadedFile::fake()->create('Original_Doc.pdf', 100, 'application/pdf');
        $this->actingAs($this->facultyA)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$fileA],
        ]);
        $docA = TaskDocument::where('file_name', 'Original_Doc.pdf')->first();

        // Faculty B attempts to replace Faculty A's document -> 403 Forbidden
        $replacementFile = UploadedFile::fake()->create('Malicious_Replace.pdf', 100, 'application/pdf');
        $resB = $this->actingAs($this->facultyB)->post(route('faculty.tasks.documents.replace', [$this->task, $docA]), [
            'document' => $replacementFile,
        ]);
        $resB->assertStatus(403);

        // Faculty A replaces her own document -> Success (v2 created)
        $validReplacement = UploadedFile::fake()->create('Original_Doc_v2.pdf', 100, 'application/pdf');
        $resA = $this->actingAs($this->facultyA)->post(route('faculty.tasks.documents.replace', [$this->task, $docA]), [
            'document' => $validReplacement,
        ]);
        $resA->assertRedirect(route('faculty.tasks.show', $this->task));

        $this->assertDatabaseHas('task_documents', [
            'file_name' => 'Original_Doc_v2.pdf',
            'version'   => 2,
            'user_id'   => $this->facultyA->id,
        ]);
    }

    public function test_document_upload_notifies_other_collaborators_and_owner()
    {
        $fileA = UploadedFile::fake()->create('Collaborative_Evidence.pdf', 100, 'application/pdf');
        $this->actingAs($this->facultyA)->post(route('faculty.tasks.documents.store', $this->task), [
            'documents' => [$fileA],
        ]);

        // Verify notification sent to Faculty B (collaborator) and HOD (owner)
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->facultyB->id,
            'type'    => 'document_uploaded',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->hod->id,
            'type'    => 'document_uploaded',
        ]);
    }
}
