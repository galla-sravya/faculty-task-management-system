<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::first();

        // Create HOD
        $hod = User::create([
            'name' => 'Dr. Smith (HOD)',
            'email' => 'hod@college.edu',
            'password' => Hash::make('password'),
            'role' => 'hod',
            'department_id' => $department->id,
            'phone' => '1234567890',
            'designation' => 'Professor & Head',
        ]);

        $department->update(['hod_id' => $hod->id]);

        // Create Faculties
        $faculties = [
            1 => 'amalaarlyn10@gmail.com',
            2 => 'faculty2@college.edu',
            3 => 'faculty3@college.edu',
            4 => 'faculty4@college.edu',
            5 => 'faculty5@college.edu',
            6 => 'deeksha040607@gmail.com',
        ];

        foreach ($faculties as $i => $email) {
            User::create([
                'name' => "Faculty $i",
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'faculty',
                'department_id' => $department->id,
                'phone' => '098765432' . $i,
                'designation' => 'Assistant Professor',
            ]);
        }
    }
}
