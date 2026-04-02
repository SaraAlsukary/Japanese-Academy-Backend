<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class SendResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp; // ✅ مهم جداً

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Code'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otpResetPassword'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
