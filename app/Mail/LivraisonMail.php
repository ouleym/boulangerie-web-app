<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LivraisonMail extends Mailable
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
            subject: 'Livraison Mail',
        );
    }

    /**
     * Get the message content definition.
     */
=======
    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mise à jour de votre livraison',
        );
    }

>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.livraison.mise-a-jour',
        );
    }

<<<<<<< HEAD
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
=======
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    public function attachments(): array
    {
        return [];
    }
}
