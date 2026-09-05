<?php

namespace App\Mail;

use App\Models\DailyMission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class DailyMissionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public DailyMission $mission;
    public string $missionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, DailyMission $mission)
    {
        $this->user = $user;
        $this->mission = $mission;
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/');
        $this->missionUrl = "{$frontendUrl}/missions";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Mission Check-in: {$this->mission->title} - FutureSelf",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/');
        $preferencesUrl = "{$frontendUrl}/missions?settings=1";

        return new Content(
            view: 'emails.daily_mission_reminder',
            text: 'emails.daily_mission_reminder_plain',
            with: [
                'user' => $this->user,
                'mission' => $this->mission,
                'missionUrl' => $this->missionUrl,
                'preferencesUrl' => $preferencesUrl,
                'streak' => $this->user->daily_streak ?? 0,
            ]
        );
    }

    /**
     * Get the headers for the message.
     */
    public function headers(): Headers
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/');
        $unsubscribeUrl = "{$frontendUrl}/missions?settings=1";

        return new Headers(
            text: [
                'List-Unsubscribe' => "<mailto:hello@futureself.in?subject=unsubscribe>, <{$unsubscribeUrl}>",
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
