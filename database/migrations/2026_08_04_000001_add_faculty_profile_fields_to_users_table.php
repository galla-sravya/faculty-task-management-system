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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('designation');
            $table->string('specialization')->nullable()->after('profile_photo_path');
            $table->string('google_scholar')->nullable()->after('specialization');
            $table->string('orcid')->nullable()->after('google_scholar');
            $table->string('google_site')->nullable()->after('orcid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_path',
                'specialization',
                'google_scholar',
                'orcid',
                'google_site',
            ]);
        });
    }
};
