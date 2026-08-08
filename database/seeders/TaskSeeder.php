<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\TaskChecklistItem::truncate();
        \App\Models\TaskActivity::truncate();
        \App\Models\TaskAuditLog::truncate();
        \App\Models\TaskDocument::truncate();
        \Illuminate\Support\Facades\DB::table('task_user')->truncate();
        Task::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $hod = User::where('role', 'hod')->first();
        $faculties = User::where('role', 'faculty')->get();
        $departmentId = $hod ? $hod->department_id : 1;
        $createdById = $hod ? $hod->id : 1;

        $tasksData = [
            [
                'title' => 'Prepare Syllabus for Next Semester',
                'description' => 'Update the course contents and reference books for 3rd year CSE students.',
                'priority' => 'high',
                'category' => 'Academics',
                'deadline' => Carbon::now()->addDays(2),
                'status' => 'pending',
            ],
            [
                'title' => 'Grade Mid-term Data Structures Papers',
                'description' => 'Complete evaluation and enter marks for Data Structures mid-term examinations.',
                'priority' => 'urgent',
                'category' => 'Academics',
                'deadline' => Carbon::now()->subDays(1), // overdue
                'status' => 'overdue',
            ],
            [
                'title' => 'Organize Annual Tech Fest 2026',
                'description' => 'Coordinate student volunteers and manage event scheduling for Tech Fest.',
                'priority' => 'medium',
                'category' => 'Department',
                'deadline' => Carbon::now()->addDays(15),
                'status' => 'in_progress',
            ],
            [
                'title' => 'Submit AI/ML Research Grant Proposal',
                'description' => 'Draft and finalize DST grant proposal for department AI lab equipment.',
                'priority' => 'high',
                'category' => 'Research',
                'deadline' => Carbon::today(), // Today's deadline
                'status' => 'pending',
            ],
            [
                'title' => 'Prepare NBA Criterion 5 Documentation',
                'description' => 'Gather faculty contribution, research output, and course outcome records for NBA accreditation.',
                'priority' => 'urgent',
                'category' => 'NBA',
                'deadline' => Carbon::now()->addDays(4),
                'status' => 'in_progress',
            ],
            [
                'title' => 'Audit NAAC Steering Committee Reports',
                'description' => 'Verify Criteria 2 and Criteria 3 submissions for NAAC peer team visit.',
                'priority' => 'high',
                'category' => 'NAAC',
                'deadline' => Carbon::now()->addDays(10),
                'status' => 'pending',
            ],
            [
                'title' => 'Conduct Campus Placement Training Session',
                'description' => 'Organize mock coding interviews and technical round preparation for final year students.',
                'priority' => 'medium',
                'category' => 'Placement',
                'deadline' => Carbon::now()->addDays(1),
                'status' => 'in_progress',
            ],
            [
                'title' => 'Host Cloud Computing Workshop',
                'description' => 'Coordinate AWS hands-on training workshop for CSE & IT students.',
                'priority' => 'low',
                'category' => 'Workshop',
                'deadline' => Carbon::now()->subDays(2),
                'status' => 'completed',
            ],
            [
                'title' => 'Faculty Development Program on Cyber Security',
                'description' => 'Attend and submit report for FDP on modern network security protocols.',
                'priority' => 'medium',
                'category' => 'Seminar',
                'deadline' => Carbon::now()->subDays(5),
                'status' => 'completed',
            ],
        ];

        foreach ($tasksData as $idx => $data) {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'category' => $data['category'],
                'deadline' => $data['deadline'],
                'status' => $data['status'],
                'created_by' => $createdById,
                'department_id' => $departmentId,
            ]);

            // Assign to 1-3 faculties
            if ($faculties->isNotEmpty()) {
                $assignees = $faculties->slice(($idx * 2) % max(1, $faculties->count()), 2);
                if ($assignees->isEmpty()) {
                    $assignees = $faculties->take(2);
                }
                foreach ($assignees as $faculty) {
                    $pStatus = $data['status'] === 'overdue' ? 'pending' : $data['status'];
                    $progress = match($pStatus) {
                        'completed' => 100,
                        'in_progress' => rand(30, 80),
                        default => 0,
                    };
                    $task->assignees()->attach($faculty->id, [
                        'status' => $pStatus,
                        'progress_percentage' => $progress,
                        'remarks' => $progress > 0 ? "Progress updated to {$progress}%" : null,
                    ]);
                }
            }
        }

        // ── Multi-Faculty Collaborative Timeline Task ──
        if ($faculties->count() >= 3) {
            $timelineFaculties = $faculties->take(3);

            $timelineTask = Task::create([
                'title' => 'Collaborative IEEE Conference Paper Review',
                'description' => 'Joint review of department research paper submissions for IEEE International Conference.',
                'priority' => 'high',
                'category' => 'Research',
                'deadline' => Carbon::now()->addDays(7),
                'created_by' => $createdById,
                'department_id' => $departmentId,
                'status' => 'in_progress',
            ]);

            $timelineTask->assignees()->attach($timelineFaculties[0]->id, [
                'status' => 'completed',
                'progress_percentage' => 100,
                'remarks' => 'Reviewed sections 1-3 and added citations.',
                'completed_at' => Carbon::now()->subDays(1),
            ]);

            $timelineTask->assignees()->attach($timelineFaculties[1]->id, [
                'status' => 'in_progress',
                'progress_percentage' => 65,
                'remarks' => 'Working on methodology section.',
            ]);

            $timelineTask->assignees()->attach($timelineFaculties[2]->id, [
                'status' => 'pending',
                'progress_percentage' => 0,
                'remarks' => null,
            ]);

            // Add sample subtasks for work division
            \App\Models\TaskChecklistItem::create([
                'task_id' => $timelineTask->id,
                'title' => 'Final documentation must be submitted for HOD review',
                'description' => 'Ensure all citations and IEEE format guidelines are verified.',
                'created_by' => $createdById,
                'created_by_role' => 'hod',
                'assigned_to' => null, // Shared / Both
                'status' => 'pending',
                'is_completed' => false,
            ]);

            \App\Models\TaskChecklistItem::create([
                'task_id' => $timelineTask->id,
                'title' => 'Collect previous paper submissions and references',
                'description' => 'Gather PDFs from repository.',
                'created_by' => $timelineFaculties[0]->id,
                'created_by_role' => 'faculty',
                'assigned_to' => $timelineFaculties[0]->id,
                'status' => 'completed',
                'is_completed' => true,
                'completed_by' => $timelineFaculties[0]->id,
                'completed_at' => Carbon::now()->subHours(5),
            ]);

            \App\Models\TaskChecklistItem::create([
                'task_id' => $timelineTask->id,
                'title' => 'Verify methodology and experimental results',
                'description' => 'Check graph figures and statistical consistency.',
                'created_by' => $timelineFaculties[0]->id,
                'created_by_role' => 'faculty',
                'assigned_to' => $timelineFaculties[1]->id,
                'status' => 'in_progress',
                'is_completed' => false,
            ]);
        }

        // Also add checklist items for task ID 1 if available
        $firstTask = Task::first();
        if ($firstTask && $faculties->count() >= 2) {
            \App\Models\TaskChecklistItem::create([
                'task_id' => $firstTask->id,
                'title' => 'Submit final syllabus document for HOD approval',
                'description' => 'Mandatory HOD requirement prior to semester start.',
                'created_by' => $createdById,
                'created_by_role' => 'hod',
                'assigned_to' => null,
                'status' => 'pending',
                'is_completed' => false,
            ]);

            \App\Models\TaskChecklistItem::create([
                'task_id' => $firstTask->id,
                'title' => 'Collect syllabus requirements from syllabus committee',
                'description' => 'Work item assigned by Dr. R. Manimegalai.',
                'created_by' => $faculties[0]->id,
                'created_by_role' => 'faculty',
                'assigned_to' => $faculties[0]->id,
                'status' => 'in_progress',
                'is_completed' => false,
            ]);

            \App\Models\TaskChecklistItem::create([
                'task_id' => $firstTask->id,
                'title' => 'Update reference books and online resource links',
                'description' => 'Assigned to Dr. S. Kalarani.',
                'created_by' => $faculties[0]->id,
                'created_by_role' => 'faculty',
                'assigned_to' => $faculties[1]->id,
                'status' => 'pending',
                'is_completed' => false,
            ]);
        }
    }
}
