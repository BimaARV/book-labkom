<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AdminUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $updatedUser;
    public $changerAdmin;
    public $changes;

    /**
     * Create a new message instance.
     */
    public function __construct(User $updatedUser, User $changerAdmin, array $changes)
    {
        $this->updatedUser = $updatedUser;
        $this->changerAdmin = $changerAdmin;
        $this->changes = $changes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Notifikasi Perubahan Data Admin - Techub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_updated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
