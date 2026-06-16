<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangeRequestProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $changeRequest;
    public $admin;

    public function __construct($changeRequest, $admin)
    {
        $this->changeRequest = $changeRequest;
        $this->admin = $admin;
    }

    public function build()
    {
        $statusText = $this->changeRequest->status === 'approved' ? 'Disetujui' : 'Ditolak';

        return $this->subject("Status Pengajuan Perubahan: {$statusText}")
                    ->view('emails.change_request_processed');
    }
}
