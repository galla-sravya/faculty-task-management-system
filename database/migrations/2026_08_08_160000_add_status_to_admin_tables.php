<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('role');
            });
        }

        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'status')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('code');
            });
        }

        if (Schema::hasTable('coordinator_types') && !Schema::hasColumn('coordinator_types', 'status')) {
            Schema::table('coordinator_types', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'status')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasTable('coordinator_types') && Schema::hasColumn('coordinator_types', 'status')) {
            Schema::table('coordinator_types', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
