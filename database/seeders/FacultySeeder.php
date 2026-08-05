<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::first();

        $faculties = [
            [
                'name' => 'Dr. B. Gomathy',
                'designation' => 'Professor & HOD (i/c)',
                'email' => 'drgomathy@psgitech.ac.in',
                'specialization' => 'Data Analytics',
                'profile_photo_path' => 'faculty/gomathy_b.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?hl=en&user=sUGFFQsAAAAJ',
                'orcid' => 'https://orcid.org/my-orcid?orcid=0000-0002-0418-2150',
                'google_site' => null,
            ],
            [
                'name' => 'Dr. R. Manimegalai',
                'designation' => 'Professor',
                'email' => 'drrm@psgitech.ac.in',
                'specialization' => 'Distributed Computing VLSI Algorithms, IoT and Security',
                'profile_photo_path' => 'faculty/1707802019_manimegalai.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=X0WcdY8AAAAJ&hl=en',
                'orcid' => 'https://orcid.org/0000-0003-1398-4080',
                'google_site' => null,
            ],
            [
                'name' => 'Dr. S. Kalarani',
                'designation' => 'Professor',
                'email' => 'kalarani@psgitech.ac.in',
                'specialization' => 'Cloud Computing, Deep Learning',
                'profile_photo_path' => 'faculty/kalarani1.jpg',
                'google_scholar' => 'https://scholar.google.co.in/citations?user=JUzxGPEAAAAJ&hl=en',
                'orcid' => 'https://orcid.org/0000-0002-7554-1130',
                'google_site' => null,
            ],
            [
                'name' => 'Dr. R. Manjula Devi',
                'designation' => 'Professor',
                'email' => 'manjuladevi.cs@psgitech.ac.in',
                'specialization' => 'Machine Learning, Image Processing, Soft Computing, AI',
                'profile_photo_path' => 'faculty/Manjula_cse.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=_u5Z9qcAAAAJ',
                'orcid' => 'https://orcid.org/0000-0002-9319-8874',
                'google_site' => null,
            ],
            [
                'name' => 'Mr. V. Harikrishnan',
                'designation' => 'Professor of Practice',
                'email' => 'harikrishnan.cs@psgitech.ac.in',
                'specialization' => 'Project Management, ERP',
                'profile_photo_path' => 'faculty/Harikrishna.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. I. Kala',
                'designation' => 'Associate Professor',
                'email' => 'kala@psgitech.ac.in',
                'specialization' => 'Mobile AdHoc Network and Database Management System',
                'profile_photo_path' => 'faculty/1707802253_kala.jpg',
                'google_scholar' => 'https://scholar.google.co.in/citations?hl=en&user=b_lma8IAAAAJ&view_op=list_works&authuser=1',
                'orcid' => 'https://orcid.org/0000-0002-6896-7707',
                'google_site' => 'https://sites.google.com/psgitech.ac.in/dr-i-kala/home',
            ],
            [
                'name' => 'Dr. K. Malarvizhi',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'malarvizhi@psgitech.ac.in',
                'specialization' => 'Machine Learning, Deep Learning',
                'profile_photo_path' => 'faculty/Malar_AI.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?view_op=list_works&hl=en&hl=en&user=nvqr_JUAAAAJ',
                'orcid' => 'https://orcid.org/0000-0002-7983-5734',
                'google_site' => null,
            ],
            [
                'name' => 'Dr. T. Kalai Selvi',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'tks.cs@psgitech.ac.in',
                'specialization' => 'Internet of Things (IoT), Data Management',
                'profile_photo_path' => 'faculty/Kalai_cse.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=6HKPEYEAAAAJ&hl=en',
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. M. N. Kavitha',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'kavitha@psgitech.ac.in',
                'specialization' => 'Machine Learning, Deep Learning & Operating System',
                'profile_photo_path' => 'faculty/kavitha.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. A. Sunitha Nandhini',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'asn@psgitech.ac.in',
                'specialization' => 'Artifical Intelligence, Internet of Things',
                'profile_photo_path' => 'faculty/1707802666_sunitha.jpg',
                'google_scholar' => 'https://scholar.google.co.in/citations?hl=en&user=aJq4onoAAAAJ',
                'orcid' => 'https://orcid.org/0000-0001-5992-1750',
                'google_site' => 'https://sites.google.com/psgitech.ac.in/sunitha-nandhini/home',
            ],
            [
                'name' => 'Ms. M. Kirubadevi',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'kirubadevi@psgitech.ac.in',
                'specialization' => 'Software Engineering',
                'profile_photo_path' => 'faculty/Kirubadevi.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. M. Sangeetha',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'sangeetha.cs@psgitech.ac.in',
                'specialization' => 'Deep Learning, Graph Neural Networks',
                'profile_photo_path' => 'faculty/sangeetha_cse.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. M. Karthigha',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'karthigha@psgitech.ac.in',
                'specialization' => 'Network Security, Augmented Reality & Virtual Reality',
                'profile_photo_path' => 'faculty/1707802886_kar.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=abJqF8sAAAAJ&hl=en&authuser=1',
                'orcid' => 'https://orcid.org/0000-0003-2069-7258',
                'google_site' => 'https://sites.google.com/view/m-karthigha/bio?authuser=1',
            ],
            [
                'name' => 'Ms. P. Jeevitha',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'jeevithap@psgitech.ac.in',
                'specialization' => 'Machine Learning',
                'profile_photo_path' => 'faculty/Jeevitha.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Ms. S. S. Saranya',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'saranya@psgitech.ac.in',
                'specialization' => 'Security, Blockchain',
                'profile_photo_path' => 'faculty/Saranya.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?hl=en&user=_JLd83UAAAAJ',
                'orcid' => 'https://orcid.org/0000-0003-2546-6280',
                'google_site' => null,
            ],
            [
                'name' => 'Dr. Sathya Balaji',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'sathyabalaji@psgitech.ac.in',
                'specialization' => 'Machine Learning, Artificial Intelligence',
                'profile_photo_path' => 'faculty/Sathya_Balaji.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Ms. P. Shanmugapriya',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'shanmugapriya@psgitech.ac.in',
                'specialization' => 'DBMS, Machine Learning , Deep Learning',
                'profile_photo_path' => 'faculty/Shanmugapriya.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Lt. V. Vilasini',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'vilasini@psgitech.ac.in',
                'specialization' => 'Data Science and Data Analytics',
                'profile_photo_path' => 'faculty/1707803189_vilasini.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. S. Vaishnavi',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'vaishnavis@psgitech.ac.in',
                'specialization' => 'Data Science, Machine Learning',
                'profile_photo_path' => 'faculty/1707803002_vaishnavi.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=6rNmGx4AAAAJ&hl=en',
                'orcid' => 'https://orcid.org/0000-0001-6011-450X',
                'google_site' => 'https://sites.google.com/view/dr-vaishnavi/home',
            ],
            [
                'name' => 'Dr. V. C. Maha Vishnu',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'mvvc@psgitech.ac.in',
                'specialization' => 'Video Data Mining and Image Analytics',
                'profile_photo_path' => 'faculty/1707803114_mahavishnu.jpg',
                'google_scholar' => 'https://scholar.google.co.in/citations?hl=en&user=QMfnpm0AAAAJ&view_op=list_works&sortby=pubdate',
                'orcid' => 'https://orcid.org/0000-0001-8430-0876',
                'google_site' => 'https://sites.google.com/view/dr-mahavishnu-v-c/home',
            ],
            [
                'name' => 'Ms. P. Gouthami',
                'designation' => 'Assistant Professor (Senior Grade)',
                'email' => 'gouthami.cs@psgitech.ac.in',
                'specialization' => 'Machine Learning, Deep Learning',
                'profile_photo_path' => 'faculty/gouthami_cse.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Ms. S. Leela',
                'designation' => 'Assistant Professor (Senior Grade)',
                'email' => 'leela.cs@psgitech.ac.in',
                'specialization' => 'Deep Learning',
                'profile_photo_path' => 'faculty/Leela_cse.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Mr. B. Ajith Jerom',
                'designation' => 'Assistant Professor',
                'email' => 'ajith@psgitech.ac.in',
                'specialization' => 'Data Science and Data Analytics',
                'profile_photo_path' => 'faculty/ajith_cse.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=0_-ed7YAAAAJ&hl=en',
                'orcid' => 'https://orcid.org/0009-0004-6194-1645',
                'google_site' => null,
            ],
            [
                'name' => 'Ms. C. Divya Gowri',
                'designation' => 'Assistant Professor',
                'email' => 'divya.cs@psgitech.ac.in',
                'specialization' => 'Data Mining, Database Management System',
                'profile_photo_path' => 'faculty/divya_gowri.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Ms. S. V. Sowthika',
                'designation' => 'Assistant Professor',
                'email' => 'sowthika.cs@psgitech.ac.in',
                'specialization' => 'Machine Learning, DBMS',
                'profile_photo_path' => 'faculty/sowthika.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Ms. R. Hemapriya',
                'designation' => 'Assistant Professor',
                'email' => 'hemapriya.cs@psgitech.ac.in',
                'specialization' => 'DBMS, Machine Learning',
                'profile_photo_path' => 'faculty/Hemapriya.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ],
            [
                'name' => 'Dr. B. Gomathi',
                'designation' => 'Associate Professor',
                'email' => 'gomathi@psgitech.ac.in',
                'specialization' => 'Cloud Computing, Machine Learning, Optimization Techniques',
                'profile_photo_path' => 'faculty/1707802340_gomathi.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?user=QYmbpmoAAAAJ&hl=en&authuser=1',
                'orcid' => 'https://orcid.org/0000-0002-3632-9858',
                'google_site' => 'https://sites.google.com/psgitech.ac.in/gomathi/',
            ],
            [
                'name' => 'Dr. P. Anantha Prabha',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'ap@psgitech.ac.in',
                'specialization' => 'Deep Learning, Cloud Computing',
                'profile_photo_path' => 'faculty/Anantha_Prabha.jpg',
                'google_scholar' => 'https://scholar.google.co.in/citations?user=yTeXBtwAAAAJ&hl=en',
                'orcid' => 'https://orcid.org/0000-0001-6565-3529',
                'google_site' => null,
            ],
            [
                'name' => 'Mr. K. S. Giriprasath',
                'designation' => 'Assistant Professor (Selection Grade)',
                'email' => 'giriprasath@psgitech.ac.in',
                'specialization' => 'Cyber security, Network Security',
                'profile_photo_path' => 'faculty/giri.jpg',
                'google_scholar' => null,
                'orcid' => null,
                'google_site' => null,
            ]
        ];

        $credentials = [];

        foreach ($faculties as $faculty) {
            // Generate clean password
            $cleanName = str_replace(['Dr.', 'Mr.', 'Ms.', 'Lt.', ' ', '.', '(', ')', '&'], '', $faculty['name']);
            $passwordText = 'password'; // Standardized password for demo consistency
            
            $role = str_contains(strtolower($faculty['designation']), 'hod') ? 'hod' : 'faculty';
            
            $user = User::updateOrCreate(
                ['email' => $faculty['email']],
                [
                    'name' => $faculty['name'],
                    'password' => Hash::make($passwordText),
                    'role' => $role,
                    'department_id' => $department ? $department->id : 1,
                    'designation' => $faculty['designation'],
                    'specialization' => $faculty['specialization'],
                    'profile_photo_path' => $faculty['profile_photo_path'],
                    'google_scholar' => $faculty['google_scholar'],
                    'orcid' => $faculty['orcid'],
                    'google_site' => $faculty['google_site'],
                    'phone' => null,
                ]
            );

            // If HOD role, assign as department HOD
            if ($role === 'hod' && $department) {
                $department->update(['hod_id' => $user->id]);
            }

            $credentials[] = [
                'name' => $faculty['name'],
                'email' => $faculty['email'],
                'password' => $passwordText,
                'role' => $role,
            ];
        }

        // Also ensure default hod@college.edu account exists for seamless demo login
        User::updateOrCreate(
            ['email' => 'hod@college.edu'],
            [
                'name' => 'Dr. B. Gomathy (HOD)',
                'password' => Hash::make('password'),
                'role' => 'hod',
                'department_id' => $department ? $department->id : 1,
                'designation' => 'Professor & HOD (i/c)',
                'specialization' => 'Data Analytics',
                'profile_photo_path' => 'faculty/gomathy_b.jpg',
                'google_scholar' => 'https://scholar.google.com/citations?hl=en&user=sUGFFQsAAAAJ',
                'orcid' => 'https://orcid.org/my-orcid?orcid=0000-0002-0418-2150',
            ]
        );

        // Save credentials to JSON file
        File::put(storage_path('app/faculty_credentials.json'), json_encode($credentials, JSON_PRETTY_PRINT));
    }
}
