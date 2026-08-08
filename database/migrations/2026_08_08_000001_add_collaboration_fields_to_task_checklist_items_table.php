<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add collaboration fields to task_checklist_items for work-division support.
     *
     * New columns:
     *  - description: optional text for subtask details
     *  - created_by: who created the checklist item
     *  - created_by_role: 'hod', 'faculty', 'nba_coordinator'
     *  - assigned_to: nullable FK to users (null = all collaborators)
     *  - status: 'pending', 'in_progress', 'completed'
     */
    public function up(): void
    {
        Schema::table('task_checklist_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->unsignedBigInteger('created_by')->nullable()->after('description');
            $table->string('created_by_role', 30)->nullable()->after('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('created_by_role');
            $table->string('status', 30)->default('pending')->after('assigned_to');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['task_id', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_checklist_items', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['task_id', 'assigned_to']);
            $table->dropColumn(['description', 'created_by', 'created_by_role', 'assigned_to', 'status']);
        });
    }
};
