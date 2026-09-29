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
            subject: "Reminder: Your mission \"{$this->mission->title}\" is still pending — FutureSelf",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_mission_reminder',
            text: 'emails.daily_mission_reminder_plain',
            with: [
                'user' => $this->user,
                'mission' => $this->mission,
                'missionUrl' => $this->missionUrl,
                'streak' => $this->user->daily_streak ?? 0,
            ]
        );
    }

    /**
     * Get the headers for the message.
     *
     * Strict inbox delivery measures:
     * - List-Unsubscribe + List-Unsubscribe-Post: required by Gmail/Yahoo
     *   for bulk senders (Feb 2024 policy).
     * - Feedback-ID: separate stream ID so reminder reputation is isolated
     *   from other mail types.
     * - X-Entity-Ref-ID: unique per email to prevent Gmail thread-collapsing.
     * - No X-Priority, X-Mailer, or Precedence headers.
     */
    public function headers(): Headers
    {
        $unsubscribeUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/') . '/missions?settings=1';

        return new Headers(
            text: [
                'List-Unsubscribe' => "<{$unsubscribeUrl}>",
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'Feedback-ID' => 'mission_reminder:futureself',
                'X-Entity-Ref-ID' => bin2hex(random_bytes(16)),
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
