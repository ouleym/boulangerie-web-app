<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FactureEnvoiMail extends Mailable
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
            subject: 'Facture Envoi Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.facture.envoi',
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
=======
    public $invoicePath;

    public function __construct($invoicePath)
    {
        $this->invoicePath = $invoicePath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre facture est disponible',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.facture_envoi',
        );
    }

    public function attachments(): array
    {
        return [
            $this->invoicePath ? $this->attach($this->invoicePath) : null,
        ];
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
