<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\NotificationLog;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCollaboratorNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hod_receives_notification_when_faculty_adds_collaborator()
    {
        $department = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);

        $hod = User::create([
            'name' => 'Dr. HOD User',
            'email' => 'hod@cse.edu',
            'password' => bcrypt('password'),
            'role' => 'hod',
            'department_id' => $department->id,
        ]);

        $faculty1 = User::create([
            'name' => 'Dr. R. Manimegalai',
            'email' => 'manimegalai@cse.edu',
            'password' => bcrypt('password'),
            'role' => 'faculty',
            'department_id' => $department->id,
        ]);

        $faculty2 = User::create([
            'name' => 'Dr. R. Manjula Devi',
            'email' => 'manjuladevi@cse.edu',
            'password' => bcrypt('password'),
            'role' => 'faculty',
            'department_id' => $department->id,
        ]);

        $otherHod = User::create([
            'name' => 'Dr. Other HOD',
            'email' => 'otherhod@ece.edu',
            'password' => bcrypt('password'),
            'role' => 'hod',
            'department_id' => Department::create(['name' => 'Electronics', 'code' => 'ECE'])->id,
        ]);

        $task = Task::create([
            'title' => 'Prepare NBA Criterion 5 Documentation',
            'description' => 'Detailed documentation needed',
            'created_by' => $hod->id,
            'department_id' => $department->id,
            'priority' => 'high',
            'status' => 'pending',
            'deadline' => now()->addDays(5),
        ]);

        // Assign faculty1 to task as owner
        $task->assignees()->attach($faculty1->id, [
            'status' => 'pending',
            'progress_percentage' => 0,
            'role' => 'owner',
            'assigned_by' => $hod->id,
            'assigned_at' => now(),
        ]);

        // Faculty1 adds Faculty2 as collaborator
        $response = $this->actingAs($faculty1)
            ->post(route('tasks.collaborators.store', $task), [
                'collaborators' => [$faculty2->id],
                'role' => 'collaborator',
                'reason' => 'Need help with Section 5.2',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check collaborator added
        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $faculty2->id,
            'role' => 'collaborator',
        ]);

        // Check assigned faculty notification
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $faculty2->id,
            'type' => 'collaborator_added',
            'reference_id' => $task->id,
        ]);

        // Check HOD notification created automatically
        $expectedMessage = 'Dr. R. Manimegalai added Dr. R. Manjula Devi as a collaborator for "Prepare NBA Criterion 5 Documentation"';
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $hod->id,
            'type' => 'collaborator_added',
            'reference_id' => $task->id,
            'reference_type' => Task::class,
            'message' => $expectedMessage,
        ]);

        // Ensure unrelated HOD did NOT receive notification
        $this->assertDatabaseMissing('notification_logs', [
            'user_id' => $otherHod->id,
        ]);

        // Check TaskActivity logged
        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'user_id' => $faculty1->id,
            'action' => 'collaborator_added',
        ]);
    }
}
