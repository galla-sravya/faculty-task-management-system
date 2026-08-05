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
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('reminder_48h_sent_at')->nullable();
            $table->dateTime('deadline_day_morning_sent_at')->nullable();
            $table->dateTime('deadline_day_evening_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_48h_sent_at',
                'deadline_day_morning_sent_at',
                'deadline_day_evening_sent_at',
            ]);
        });
    }
};
