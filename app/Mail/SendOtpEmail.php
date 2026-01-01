<?php

namespace App\Mail;

// We no longer need Queueable, ShouldQueue, or SerializesModels
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Mailable for sending the One-Time Password (OTP)
 * This version sends synchronously (immediately).
 */
class SendOtpEmail extends Mailable
{
    // We no longer need the 'use Queueable, SerializesModels;' traits

    /**
     * The OTP code to be sent.
     *
     * @var string
     */
    public $code;

    /**
     * Create a new message instance.
     *
     * @param string $code The 6-digit OTP code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     * This defines the "from" address and the subject.
     */
    public function envelope(): Envelope
    {
        // Meets supervisor rule: "Subject nabayad khali bashad, kutah va vazeh"
        // (Subject must not be empty, short and clear)
        return new Envelope(
            subject: 'Your One-Time Password (OTP)',
        );
    }

    /**
     * Get the message content definition.
     * This points to our simple Blade template.
     */
    public function content(): Content
    {
        // Meets supervisor rules about simple text, no ads, and clear OTP.
        return new Content(
            markdown: 'emails.otp', // Points to the view file
            with: [
                'otpCode' => $this->code, // Passes the code to the view
            ],
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

