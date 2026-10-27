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

class NewContactQueryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Contact $contact;
    public string $adminContactsUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
        $this->adminContactsUrl = url('/admin/contacts');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $categoryLabel = ucfirst($this->contact->category ?? 'General');
        $subjectText = $this->contact->subject ?: 'New Query';

        return new Envelope(
            subject: "[FutureSelf Query] [{$categoryLabel}] {$subjectText} - from {$this->contact->name}",
            replyTo: [
                new Address($this->contact->email, $this->contact->name),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_contact_query',
            text: 'emails.new_contact_query_plain',
            with: [
                'contact' => $this->contact,
                'adminContactsUrl' => $this->adminContactsUrl,
            ],
        );
    }
}
