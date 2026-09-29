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
            // Tracks when the user last completed a daily mission (used to detect 3-day inactivity)
            $table->timestamp('last_mission_completed_at')->nullable()->after('mission_reminder_enabled');

            // Tracks when the last feedback reminder email was sent (used for 2-day interval)
            $table->timestamp('last_feedback_reminder_sent_at')->nullable()->after('last_mission_completed_at');

            // Tracks when the last motivational email was sent (prevents duplicate sends on same day)
            $table->timestamp('last_motivational_mail_sent_at')->nullable()->after('last_feedback_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_mission_completed_at',
                'last_feedback_reminder_sent_at',
                'last_motivational_mail_sent_at',
            ]);
        });
    }
};
