<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class NewAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $newAdmin;
    public $creatorAdmin;

    public $rawPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(User $newAdmin, User $creatorAdmin, $rawPassword = null)
    {
        $this->newAdmin = $newAdmin;
        $this->creatorAdmin = $creatorAdmin;
        $this->rawPassword = $rawPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Selamat Datang di Techub - Akun Admin Baru',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_admin',
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
