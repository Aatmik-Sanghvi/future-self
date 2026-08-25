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
            $table->boolean('mission_email_enabled')->default(true)->after('daily_streak');
            $table->string('mission_reminder_time', 10)->default('19:00')->after('mission_email_enabled');
            $table->boolean('mission_reminder_enabled')->default(true)->after('mission_reminder_time');
        });

        Schema::table('daily_missions', function (Blueprint $table) {
            $table->timestamp('morning_mail_sent_at')->nullable()->after('reflection');
            $table->timestamp('reminder_mail_sent_at')->nullable()->after('morning_mail_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mission_email_enabled', 'mission_reminder_time', 'mission_reminder_enabled']);
        });

        Schema::table('daily_missions', function (Blueprint $table) {
            $table->dropColumn(['morning_mail_sent_at', 'reminder_mail_sent_at']);
        });
    }
};
