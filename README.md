# Enterprise Faculty Task Lifecycle Management System

An enterprise-grade Laravel web application for academic departments to manage, track, assign, collaborate, and monitor faculty tasks, meetings, performance reports, document submissions, audit logs, and subtask checklists.

---

## Key Enterprise Modules Implemented

1. **HOD Task Editing & Selective Change Detection**: HODs can edit task details; field-level changes are detected and logged selectively into audit history.
2. **Immutable Audit History**: Dedicated Audit History tab recording `Task`, `User`, `Field`, `Old Value`, `New Value`, and timestamps for complete compliance.
3. **Task Archiving & Restore**: Soft-delete archiving (`Task::onlyTrashed()`) preserves progress, documents, collaborators, and history. Archived tasks can be restored by HODs.
4. **Safe Delete Policy**: Prevents permanent hard deletion of tasks containing progress, documents, collaborators, or history.
5. **Document Versioning & Approval Workflow**: Document replacement auto-increments versioning (`v1` → `v2`). HOD can Approve, Request Changes with review comments, or Reject documents.
6. **Task Subtasks & Checklist**: HODs can assign subtasks. Faculty completion toggles auto-calculate overall task completion progress.
7. **Threaded Discussions & Comments**: Discussion section on tasks with optional file attachment uploads.
8. **Interactive Calendar**: Visual schedule for task deadlines, assignments, and department meetings (`/calendar`).
9. **Global Multi-Field Search**: Header search bar searching across Faculty, Tasks, Categories, Priorities, Meetings, Documents, and Statuses (`/search`).
10. **Advanced Reports & CSV Export**: Download CSV exports for Tasks and Faculty Performance reports (`/hod/reports/export/csv`).
11. **Enterprise Dashboard Widgets**: Includes Recently Completed Tasks, Recent Activity Timeline Stream, Task Status Distribution, and Awaiting Review Documents.
12. **NBA Coordinator Role & Module Administration**: Role-Based Access Control (RBAC) allowing NBA Coordinators to create, assign, manage, and review NBA-specific tasks and meetings without accessing non-NBA HOD features.

---

## Technologies Used

- **Framework**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade Templates, Bootstrap 5, Bootstrap Icons, Chart.js
- **Database**: SQLite / MySQL / PostgreSQL
- **Build Tool**: Vite (Vanilla CSS & JS)

---

## Installation & Setup Instructions

```bash
# 1. Install Dependencies
composer install
npm install

# 2. Configure Environment
cp .env.example .env
php artisan key:generate

# 3. Database Setup & Seed
php artisan migrate:fresh --seed

# 4. Storage Link
php artisan storage:link

# 5. Run Server
php artisan serve
```

Visit the application at: **`http://127.0.0.1:8000`**

---

## Default Login Credentials

All demo accounts use password: **`password`**

- **HOD Account**: `drgomathy@psgitech.ac.in` (Password: `password`)
- **Faculty Account**: `drrm@psgitech.ac.in` (Password: `password`)

---

## Automated Test Verification

Run full PHPUnit / Pest test suite:
```bash
php artisan test
```
*(61 tests passed, 186 assertions)*

---

## Important Notes for Developers

1. **Storage Link**: Always ensure `php artisan storage:link` is executed so uploaded document files and profile avatars in `storage/app/public` are accessible via `/storage`.
2. **Automated Tests**: Comprehensive feature tests exist in `tests/Feature/`. Run `php artisan test` to verify logic integrity before committing new features.
