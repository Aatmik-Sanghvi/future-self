<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactQueryReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Contact $contact;
    public string $replySubject;
    public string $replyMessage;
    public string $adminName;
    public string $frontendUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Contact $contact, string $replySubject, string $replyMessage, ?string $adminName = null)
    {
        $this->contact = $contact;
        $this->replySubject = $replySubject;
        $this->replyMessage = $replyMessage;
        $this->adminName = $adminName ?: config('mail.support.name', 'FutureSelf Support');
        $this->frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.support.address')
            ?: env('SUPPORT_MAIL_FROM_ADDRESS')
            ?: env('ADMIN_CONTACT_EMAIL')
            ?: 'support@mail.futureself.in';

        $fromName = config('mail.support.name')
            ?: env('SUPPORT_MAIL_FROM_NAME')
            ?: 'FutureSelf Support';

        $replyToAddress = env('ADMIN_CONTACT_EMAIL', $fromAddress);

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address($replyToAddress, $fromName),
            ],
            subject: $this->replySubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_query_reply',
            text: 'emails.contact_query_reply_plain',
            with: [
                'contact' => $this->contact,
                'replySubject' => $this->replySubject,
                'replyMessage' => $this->replyMessage,
                'adminName' => $this->adminName,
                'frontendUrl' => $this->frontendUrl,
            ],
        );
    }
}
