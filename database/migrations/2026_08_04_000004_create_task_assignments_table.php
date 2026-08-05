<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create task_assignments table
        if (!Schema::hasTable('task_assignments')) {
            Schema::create('task_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('faculty_id');
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->enum('role', ['owner', 'secondary_owner', 'collaborator'])->default('collaborator');
                $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
                $table->text('reason')->nullable();
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamps();

                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
                $table->foreign('faculty_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
                $table->unique(['task_id', 'faculty_id']);
            });
        }

        // 2. Add role, assigned_by, assigned_at to task_user pivot table for seamless Eloquent pivot usage
        Schema::table('task_user', function (Blueprint $table) {
            if (!Schema::hasColumn('task_user', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('user_id');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('task_user', 'role')) {
                $table->enum('role', ['owner', 'secondary_owner', 'collaborator'])->default('collaborator')->after('assigned_by');
            }
            if (!Schema::hasColumn('task_user', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('completed_at');
            }
        });

        // 3. Populate task_assignments and backfill pivot data for existing records
        $existingPivot = DB::table('task_user')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->select('task_user.*', 'tasks.created_by as task_creator')
            ->get();

        foreach ($existingPivot as $row) {
            $assignedBy = $row->assigned_by ?? $row->task_creator;
            $role = $row->role ?? 'owner';

            // Update task_user pivot
            DB::table('task_user')
                ->where('id', $row->id)
                ->update([
                    'assigned_by' => $assignedBy,
                    'role' => $role,
                    'assigned_at' => $row->assigned_at ?? $row->created_at ?? now(),
                ]);

            // Insert into task_assignments if not exists
            DB::table('task_assignments')->updateOrInsert(
                ['task_id' => $row->task_id, 'faculty_id' => $row->user_id],
                [
                    'assigned_by' => $assignedBy,
                    'role' => $role,
                    'status' => $row->status ?? 'pending',
                    'assigned_at' => $row->assigned_at ?? $row->created_at ?? now(),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
        Schema::table('task_user', function (Blueprint $table) {
            if (Schema::hasColumn('task_user', 'assigned_by')) {
                $table->dropForeign(['assigned_by']);
                $table->dropColumn('assigned_by');
            }
            if (Schema::hasColumn('task_user', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('task_user', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });
    }
};
