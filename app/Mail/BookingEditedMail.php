<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\User;

class BookingEditedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $admin;
    public $changes;

    public function __construct(Booking $booking, User $admin, array $changes)
    {
        $this->booking = $booking;
        $this->admin = $admin;
        $this->changes = $changes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Notifikasi Perubahan Data Booking - Techub',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_edited',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
