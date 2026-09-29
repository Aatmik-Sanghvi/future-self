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

class FeedbackReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $feedbackUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/');
        $this->feedbackUrl = "{$frontendUrl}/feedback";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "A quick favor, {$this->user->name}? Your feedback shapes FutureSelf",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback_reminder',
            text: 'emails.feedback_reminder_plain',
            with: [
                'user' => $this->user,
                'feedbackUrl' => $this->feedbackUrl,
            ]
        );
    }

    /**
     * Get the headers for the message.
     *
     * Strict inbox delivery measures:
     * - List-Unsubscribe + List-Unsubscribe-Post: required by Gmail/Yahoo
     *   for bulk senders (Feb 2024 policy).
     * - Feedback-ID: separate stream ID so feedback request reputation
     *   is isolated from other mail types.
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
                'Feedback-ID' => 'feedback_request:futureself',
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
