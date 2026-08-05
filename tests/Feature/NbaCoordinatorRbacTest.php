<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NbaCoordinatorRbacTest extends TestCase
{
    use RefreshDatabase;

    protected User $hod;
    protected User $nbaCoord1;
    protected User $nbaCoord2;
    protected User $faculty;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);

        $this->hod = User::create([
            'name'          => 'Dr. HOD User',
            'email'         => 'hod@psgitech.ac.in',
            'password'      => bcrypt('password'),
            'role'          => 'hod',
            'department_id' => $this->department->id,
        ]);

        $this->nbaCoord1 = User::create([
            'name'          => 'Dr. K. Malarvizhi (NBA Coord 1)',
            'email'         => 'malarvizhi@psgitech.ac.in',
            'password'      => bcrypt('kmalarvizhi424'),
            'role'          => 'nba_coordinator',
            'department_id' => $this->department->id,
        ]);

        $this->nbaCoord2 = User::create([
            'name'          => 'Dr. M. Karthigha (NBA Coord 2)',
            'email'         => 'karthigha@psgitech.ac.in',
            'password'      => bcrypt('mkarthigha386'),
            'role'          => 'nba_coordinator',
            'department_id' => $this->department->id,
        ]);

        $this->faculty = User::create([
            'name'          => 'Dr. R. Manimegalai',
            'email'         => 'drrm@psgitech.ac.in',
            'password'      => bcrypt('rmanimegalai480'),
            'role'          => 'faculty',
            'department_id' => $this->department->id,
        ]);
    }

    public function test_dashboard_redirects_nba_coordinator_to_nba_dashboard()
    {
        $response = $this->actingAs($this->nbaCoord1)->get(route('dashboard'));
        $response->assertRedirect(route('nba.dashboard'));
    }

    public function test_nba_coordinator_cannot_access_hod_routes()
    {
        // NBA Coordinator attempting HOD Dashboard -> 403 Forbidden
        $response = $this->actingAs($this->nbaCoord1)->get(route('hod.dashboard'));
        $response->assertStatus(403);

        // NBA Coordinator attempting HOD Faculty management -> 403 Forbidden
        $response = $this->actingAs($this->nbaCoord1)->get(route('hod.faculty.index'));
        $response->assertStatus(403);
    }

    public function test_task_ownership_and_edit_permissions()
    {
        // 1. HOD creates Task A
        $hodTask = Task::create([
            'title'         => 'HOD Department Task A',
            'description'   => 'Department wide work',
            'created_by'    => $this->hod->id,
            'owner_role'    => 'hod',
            'department_id' => $this->department->id,
            'priority'      => 'high',
            'status'        => 'pending',
            'deadline'      => now()->addDays(5),
        ]);
        $hodTask->assignees()->attach($this->faculty->id, ['status' => 'pending', 'progress_percentage' => 0]);

        // 2. NBA Coord 1 creates Task B
        $nbaTask = Task::create([
            'title'         => 'NBA Criterion 5 Task B',
            'description'   => 'NBA Accreditation work',
            'created_by'    => $this->nbaCoord1->id,
            'owner_role'    => 'nba_coordinator',
            'department_id' => $this->department->id,
            'priority'      => 'urgent',
            'status'        => 'pending',
            'deadline'      => now()->addDays(3),
        ]);
        $nbaTask->assignees()->attach($this->faculty->id, ['status' => 'pending', 'progress_percentage' => 0]);

        // Test Policy:
        // HOD creates Task A -> NBA Coordinator CANNOT edit it
        $this->assertFalse($this->nbaCoord1->can('update', $hodTask));

        // NBA Coord 1 creates Task B -> NBA Coord 1 CAN edit it
        $this->assertTrue($this->nbaCoord1->can('update', $nbaTask));

        // NBA Coord 1 creates Task B -> HOD CAN edit it
        $this->assertTrue($this->hod->can('update', $nbaTask));

        // NBA Coord 1 creates Task B -> NBA Coord 2 CANNOT edit it
        $this->assertFalse($this->nbaCoord2->can('update', $nbaTask));
    }

    public function test_nba_coordinator_reports_contain_only_their_own_tasks()
    {
        // HOD Task
        Task::create([
            'title'         => 'HOD Exclusive Task',
            'created_by'    => $this->hod->id,
            'owner_role'    => 'hod',
            'department_id' => $this->department->id,
            'priority'      => 'medium',
            'status'        => 'pending',
            'deadline'      => now()->addDays(5),
        ]);

        // NBA Coord 1 Task
        Task::create([
            'title'         => 'NBA Coord 1 Task',
            'created_by'    => $this->nbaCoord1->id,
            'owner_role'    => 'nba_coordinator',
            'department_id' => $this->department->id,
            'priority'      => 'high',
            'status'        => 'pending',
            'deadline'      => now()->addDays(5),
        ]);

        $response = $this->actingAs($this->nbaCoord1)->get(route('nba.reports.index'));
        $response->assertOk();
        $response->assertSee('NBA Coord 1 Task');
        $response->assertDontSee('HOD Exclusive Task');
    }

    public function test_nba_coordinator_can_create_and_manage_own_tasks()
    {
        $response = $this->actingAs($this->nbaCoord1)->post(route('nba.tasks.store'), [
            'title'       => 'Prepare NBA SAR Report',
            'description' => 'Collect Criterion 1 to 5',
            'category'    => 'NBA Accreditation',
            'priority'    => 'high',
            'deadline'    => now()->addDays(10)->format('Y-m-d H:i'),
            'assignees'   => [$this->faculty->id],
        ]);

        $response->assertRedirect(route('nba.tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'title'      => 'Prepare NBA SAR Report',
            'created_by' => $this->nbaCoord1->id,
            'owner_role' => 'nba_coordinator',
        ]);
    }
}
