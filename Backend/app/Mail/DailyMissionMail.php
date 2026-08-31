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

class DailyMissionMail extends Mailable implements ShouldQueue
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
        $frontendUrl = rtrim(env('FRONTEND_URL', 'https://futureself.in'), '/');
        $this->missionUrl = "{$frontendUrl}/missions";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Daily Mission: {$this->mission->title} — FutureSelf",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_mission',
            text: 'emails.daily_mission_plain',
            with: [
                'user' => $this->user,
                'mission' => $this->mission,
                'missionUrl' => $this->missionUrl,
                'reminderTime' => $this->user->mission_reminder_time ?? '19:00',
            ]
        );
    }

    /**
     * Get the headers for the message.
     */
    public function headers(): Headers
    {
        $unsubscribeUrl = rtrim(env('FRONTEND_URL', 'https://futureself.in'), '/') . '/missions?settings=1';

        return new Headers(
            text: [
                'List-Unsubscribe' => "<{$unsubscribeUrl}>",
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
