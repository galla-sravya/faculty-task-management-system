<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'owner_role')) {
                $table->string('owner_role')->default('hod')->after('created_by');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('faculty')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'owner_role')) {
                $table->dropColumn('owner_role');
            }
        });
    }
};
