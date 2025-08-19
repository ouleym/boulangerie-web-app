<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromotionMail extends Mailable
{
    use Queueable, SerializesModels;

<<<<<<< HEAD
    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Promotion Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.promotion.nouvelle',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
=======
    public $promotion;

    public function __construct($promotion)
    {
        $this->promotion = $promotion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle promotion à découvrir !',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.promotion',
        );
    }

>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    public function attachments(): array
    {
        return [];
    }
}
