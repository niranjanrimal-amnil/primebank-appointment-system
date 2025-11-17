<?php

namespace AppointmentSystem\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The OTP code.
     *
     * @var string
     */
    public $otpCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $otpCode)
    {
        $this->otpCode = $otpCode;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Your OTP Code',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        // We will create this view file next.
        // It will look for 'resources/views/mail/otp-email.blade.php'
        return new Content(
            markdown: 'appointment-system::mail.otp-email',
            with: [
                'otp' => $this->otpCode,
            ],
        );
    }
}