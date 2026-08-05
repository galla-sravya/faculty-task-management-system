<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $hod = User::where('role', 'hod')->first();
        $faculties = User::where('role', 'faculty')->get();
        $departmentId = $hod->department_id;

        $meeting = Meeting::create([
            'title' => 'Department Review Meeting',
            'description' => 'Monthly review of syllabus coverage and student performance.',
            'scheduled_at' => Carbon::now()->addDays(3)->setHour(10)->setMinute(0),
            'venue' => 'Conference Room A',
            'agenda' => "1. Syllabus coverage\n2. Upcoming exams\n3. Budget discussion",
            'organized_by' => $hod->id,
            'department_id' => $departmentId,
            'status' => 'scheduled',
        ]);

        $meeting->attendees()->attach($faculties->pluck('id'), ['attendance' => 'invited']);
    }
}
