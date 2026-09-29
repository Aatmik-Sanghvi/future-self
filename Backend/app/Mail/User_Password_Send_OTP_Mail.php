<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class User_Password_Send_OTP_Mail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $otp;
    public $subject;
    /**
     * Create a new message instance.
     */
    public function __construct($name, $otp, $subject)
    {
        $this->name = $name;
        $this->otp = $otp;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp_mail',
            text: 'emails.otp_mail_plain',
            with: [
                'name' => $this->name,
                'otp' => $this->otp,
            ]
        );
    }

    /**
     * Get the headers for the message.
     *
     * OTP is transactional: no List-Unsubscribe, no Precedence: bulk.
     * - X-Entity-Ref-ID: unique per email to prevent Gmail from threading
     *   unrelated OTP emails together (which can push to spam).
     * - No X-Priority header: even "normal" (3) can be suspicious; omitting
     *   it entirely is safest.
     * - No X-Mailer: custom mailer headers add no value and can trigger
     *   heuristic spam filters.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Entity-Ref-ID' => bin2hex(random_bytes(16)),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

