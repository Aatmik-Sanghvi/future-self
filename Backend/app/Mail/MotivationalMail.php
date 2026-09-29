<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class MotivationalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $aiSubject;
    public string $aiGreeting;
    public string $aiBody;
    public string $aiActionableStep;
    public string $aiClosing;
    public string $missionUrl;
    public int $inactiveDays;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, array $aiContent, int $inactiveDays = 3)
    {
        $this->user = $user;
        $this->aiSubject = $aiContent['subject'] ?? "Hey {$user->name}, your future self is thinking of you";
        $this->aiGreeting = $aiContent['greeting'] ?? "Hey {$user->name},";
        $this->aiBody = $aiContent['body'] ?? '';
        $this->aiActionableStep = $aiContent['actionable_step'] ?? '';
        $this->aiClosing = $aiContent['closing'] ?? 'Your Future Self';
        $this->inactiveDays = $inactiveDays;

        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/');
        $this->missionUrl = "{$frontendUrl}/missions";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->aiSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.motivational',
            text: 'emails.motivational_plain',
            with: [
                'user' => $this->user,
                'aiGreeting' => $this->aiGreeting,
                'aiBody' => $this->aiBody,
                'aiActionableStep' => $this->aiActionableStep,
                'aiClosing' => $this->aiClosing,
                'missionUrl' => $this->missionUrl,
                'inactiveDays' => $this->inactiveDays,
            ]
        );
    }

    /**
     * Get the headers for the message.
     *
     * Strict inbox delivery measures:
     * - List-Unsubscribe + List-Unsubscribe-Post: required by Gmail/Yahoo
     *   for bulk senders (Feb 2024 policy).
     * - Feedback-ID: separate stream ID so motivational email reputation
     *   is isolated from other mail types.
     * - X-Entity-Ref-ID: unique per email to prevent Gmail thread-collapsing
     *   (critical for motivational mails which have AI-generated content that
     *   varies each time — threading them confuses engagement signals).
     * - No X-Priority, X-Mailer, or Precedence headers.
     */
    public function headers(): Headers
    {
        $unsubscribeUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/') . '/missions?settings=1';

        return new Headers(
            text: [
                'List-Unsubscribe' => "<{$unsubscribeUrl}>",
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'Feedback-ID' => 'motivational:futureself',
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
