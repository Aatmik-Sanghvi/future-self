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
        $rawTime = $this->user->mission_reminder_time ?? '19:00';
        try {
            $formattedReminderTime = \Carbon\Carbon::createFromFormat('H:i', substr($rawTime, 0, 5))->format('g:i A');
        } catch (\Throwable $e) {
            $formattedReminderTime = '7:00 PM';
        }

        return new Content(
            view: 'emails.daily_mission',
            text: 'emails.daily_mission_plain',
            with: [
                'user' => $this->user,
                'mission' => $this->mission,
                'missionUrl' => $this->missionUrl,
                'reminderTime' => $formattedReminderTime,
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
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'Precedence' => 'bulk',
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
