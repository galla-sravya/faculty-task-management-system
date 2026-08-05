# Faculty Task Management & Activity Monitor System

An enterprise-grade Laravel web application for academic departments to manage, track, assign, collaborate, and monitor faculty tasks, meetings, performance reports, and document submissions.

---

## Features Implemented

### 1. HOD Dashboard & Analytics
- **Real-time Performance Metrics**: Stat cards for Total Tasks, Completed Tasks, In-Progress Tasks, and Overdue Tasks.
- **Chart.js Visualizations**: Task status distribution chart and total completion gauge.
- **Advanced Filtering**: Filter tasks by Faculty, Status, Priority, Category, Deadline range, or Keyword search with instantaneous AJAX updates.
- **Sidebar Widgets**: Upcoming Meetings list and **Documents Awaiting Review** widget.

### 2. Task Assignment & Collaboration Workflow
- **Task Creation & Management**: Assign tasks to multiple faculty members with priority levels, categories, assigned dates, and deadlines.
- **Collaborator Assignment / Reassignment**: Add faculty collaborators with specific roles (`Owner`, `Secondary Owner`, `Collaborator`).
- **Separate Date Columns**: Clean tabular layout separating **Assigned Date** and **Deadline** across all tables.

### 3. Document Submission & Review System
- **Faculty Document Management**: Faculty members can upload single or multiple documents as drafts.
- **Document Versioning**: Uploading replacement files automatically increments version numbers (`v1` → `v2` → `v3`) while preserving complete version history.
- **Submit for Review**: Batch submit draft documents for HOD review, automatically updating task status to `Pending Review`.
- **HOD Review Workflow**: HOD can Approve ✅, Request Changes 🔄, or Reject ❌ documents with feedback comments.
- **Previews & Downloads**: In-browser preview for supported files (PDF, images) and direct downloads.

### 4. Meetings & Reports
- **Meeting Management**: Schedule department meetings, track attendance, and log minutes.
- **Faculty Performance Reports**: Detailed performance reports for HODs to evaluate individual faculty workload and completion rates.

### 5. Automated Notifications & Activity Log
- **Activity Log Timeline**: Comprehensive chronological history of task assignments, progress updates, collaborator changes, document uploads, and reviews.
- **Notifications**: Automated in-app notifications (`NotificationLog`) for assignments, collaborator additions, and document review statuses.

---

## Technologies Used

- **Framework**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade Templates, Bootstrap 5, Bootstrap Icons, Chart.js
- **Database**: SQLite / MySQL / PostgreSQL
- **Build Tool**: Vite (Vanilla CSS & JS)

---

## Prerequisites

- **PHP**: `^8.2` (with PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON extensions)
- **Composer**: `^2.0`
- **Node.js**: `^18.0` or `^20.0`
- **NPM**: `^9.0` or `^10.0`

---

## Installation & Setup Instructions

Follow these step-by-step instructions to set up the project locally:

### 1. Extract the Project
Extract `Faculty_Task_Management_System_Updated.zip` into your web server / working directory.

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Configure Environment
Copy `.env.example` to create `.env`:
```bash
cp .env.example .env
```
Generate the application key:
```bash
php artisan key:generate
```

### 5. Database Setup & Migrations
Create an empty SQLite database file (if using default SQLite configuration):
```bash
# For Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force

# For Unix/Linux/Mac
touch database/database.sqlite
```

Run database migrations and seeders:
```bash
php artisan migrate:fresh --seed
```

### 6. Create Storage Symlink
Create a symlink for user-uploaded documents and faculty profile photos:
```bash
php artisan storage:link
```

### 7. Run Development Server
Start the Laravel development server:
```bash
php artisan serve
```

In a separate terminal, start Vite frontend asset compilation (if modifying assets):
```bash
npm run dev
```

Visit the application at: **`http://127.0.0.1:8000`**

---

## Default Login Credentials

All demo accounts use the standard password: **`password`**

### HOD Account
- **Email**: `drgomathy@psgitech.ac.in` (or `hod@college.edu`)
- **Password**: `password`
- **Role**: HOD (Head of Department)

### Faculty Accounts
- **Email**: `drrm@psgitech.ac.in` (Prof. Dr. R. Manimegalai)
- **Email**: `kalarani@psgitech.ac.in` (Prof. Dr. S. Kalarani)
- **Email**: `manjuladevi.cs@psgitech.ac.in` (Prof. Dr. R. Manjula Devi)
- **Password**: `password`
- **Role**: Faculty

*(Additional faculty credentials can be found in `storage/app/faculty_credentials.json` after running seeders).*

---

## Essential Artisan & NPM Commands

| Command | Purpose |
|---------|---------|
| `composer install` | Install backend dependencies |
| `npm install` | Install frontend dependencies |
| `php artisan key:generate` | Generate application secret key |
| `php artisan migrate:fresh --seed` | Wipe, re-migrate, and seed demo database |
| `php artisan storage:link` | Link public storage directory |
| `php artisan test` | Run automated PHPUnit/Pest test suite |
| `php artisan route:list` | Inspect all registered application routes |
| `php artisan serve` | Start local development server |
| `npm run dev` | Run Vite asset bundler in dev mode |
| `npm run build` | Compile production assets |

---

## Directory Structure Overview

```
c:\Project\activity-monitor-main/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Faculty/          # Faculty controllers (TaskController, TaskDocumentController, etc.)
│   │   │   ├── HOD/              # HOD controllers (DashboardController, TaskController, TaskDocumentController, etc.)
│   │   │   └── ...
│   ├── Models/                   # Eloquent models (User, Task, TaskDocument, TaskActivity, etc.)
│   ├── Policies/                 # Task and Meeting access policies
│   └── Services/                 # Task filter services
├── bootstrap/                    # Application bootstrap & configuration
├── config/                       # Application config files
├── database/
│   ├── database.sqlite           # Local SQLite database file
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── public/                       # Static public assets & uploads symlink
├── resources/
│   ├── views/
│   │   ├── components/           # Blade components (stat-card, progress-ring, task-timeline)
│   │   ├── faculty/              # Faculty Blade views
│   │   ├── hod/                  # HOD Blade views
│   │   └── layouts/              # Shared application layout
├── routes/                       # Web and auth route definitions
├── storage/                      # File storage (task_documents, faculty profile photos)
└── tests/                        # Feature and unit test suites
```

---

## Important Notes for Developers

1. **Storage Link**: Always ensure `php artisan storage:link` is executed so uploaded document files and profile avatars in `storage/app/public` are accessible via `/storage`.
2. **Automated Tests**: Comprehensive feature tests exist in `tests/Feature/TaskCollaboratorTest.php` and `tests/Feature/TaskDocumentWorkflowTest.php`. Run `php artisan test` to verify logic integrity before committing new features.
3. **Database Agnostic**: The application is tested with SQLite and MySQL. Standard Eloquent relationships and query scopes are used throughout.
