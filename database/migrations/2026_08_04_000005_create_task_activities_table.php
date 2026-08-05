<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('task_activities')) {
            Schema::create('task_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action'); // assigned, collaborator_added, progress_updated, status_changed, completed
                $table->text('description');
                $table->timestamps();

                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Backfill initial activity log for existing tasks
        $tasks = DB::table('tasks')->get();
        foreach ($tasks as $task) {
            $creatorName = DB::table('users')->where('id', $task->created_by)->value('name') ?? 'HOD';

            // Initial assignment activity
            DB::table('task_activities')->updateOrInsert(
                [
                    'task_id' => $task->id,
                    'action' => 'assigned',
                ],
                [
                    'user_id' => $task->created_by,
                    'description' => "Task created and assigned by {$creatorName}.",
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at,
                ]
            );

            // If task is completed
            if ($task->status === 'completed') {
                DB::table('task_activities')->updateOrInsert(
                    [
                        'task_id' => $task->id,
                        'action' => 'completed',
                    ],
                    [
                        'user_id' => $task->created_by,
                        'description' => "Task marked as completed.",
                        'created_at' => $task->updated_at,
                        'updated_at' => $task->updated_at,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activities');
    }
};
