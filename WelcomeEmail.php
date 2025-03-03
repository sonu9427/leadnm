<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailmessage;
    public $subject;
    public $fromEmail;


    /**
     * Create a new message instance.
     */
    public function __construct($message, $subject, $fromEmail)
    {
        $this->mailmessage = $message;
        $this->subject = $subject;
        $this->fromEmail = $fromEmail; 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // return new Envelope(
        //     subject: $this->subject,
        // );
      //  $fromEmail = \App\Models\User::find(1)->email; 
        return new Envelope(
            subject: $this->subject,
            from: $this->fromEmail,  // Set the 'from' email dynamically
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.test',  // The view for the email
            with: [
                'message' => $this->mailmessage,  // Dynamic content passed to the view
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

