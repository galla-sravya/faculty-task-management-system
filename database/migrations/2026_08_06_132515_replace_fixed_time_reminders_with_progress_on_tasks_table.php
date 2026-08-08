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
            $table->dropColumn([
                'reminder_48h_sent_at',
                'reminder_12h_sent_at',
                'reminder_2h_sent_at',
                'deadline_day_morning_sent_at',
                'deadline_day_evening_sent_at',
            ]);
            $table->timestamp('reminder_50pct_sent_at')->nullable()->after('deadline');
            $table->timestamp('reminder_75pct_sent_at')->nullable()->after('reminder_50pct_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['reminder_50pct_sent_at', 'reminder_75pct_sent_at']);
            $table->timestamp('reminder_48h_sent_at')->nullable();
            $table->timestamp('reminder_12h_sent_at')->nullable();
            $table->timestamp('reminder_2h_sent_at')->nullable();
            $table->timestamp('deadline_day_morning_sent_at')->nullable();
            $table->timestamp('deadline_day_evening_sent_at')->nullable();
        });
    }
};
