<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add document review and versioning fields to the task_documents table.
     */
    public function up(): void
    {
        Schema::table('task_documents', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('file_path');
            $table->unsignedBigInteger('original_document_id')->nullable()->after('version');
            $table->text('remarks')->nullable()->after('original_document_id');
            $table->string('review_status', 30)->default('draft')->after('remarks');
            $table->text('review_comments')->nullable()->after('review_status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_comments');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->unsignedBigInteger('file_size')->nullable()->after('reviewed_at');
            $table->string('file_type', 100)->nullable()->after('file_size');

            $table->foreign('original_document_id')->references('id')->on('task_documents')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['task_id', 'review_status']);
            $table->index(['review_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_documents', function (Blueprint $table) {
            $table->dropForeign(['original_document_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['task_id', 'review_status']);
            $table->dropIndex(['review_status']);
            $table->dropColumn([
                'version', 'original_document_id', 'remarks', 'review_status',
                'review_comments', 'reviewed_by', 'reviewed_at', 'file_size', 'file_type',
            ]);
        });
    }
};
