<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Commande;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class FactureEnvoiMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $commande;
    public $user;

    public function __construct(Commande $commande, User $user)
    {
        $this->commande = $commande;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Facture - Commande #{$this->commande->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.facture-envoi',
            with: [
                'commande' => $this->commande,
                'user' => $this->user,
            ]
        );
    }

    public function attachments(): array
    {
        // Génération du PDF de la facture
        $pdf = PDF::loadView('factures.pdf', [
            'commande' => $this->commande,
            'user' => $this->user,
        ]);

        $fileName = "facture_commande_{$this->commande->id}.pdf";

        return [
            Attachment::fromData(fn () => $pdf->output(), $fileName)
                ->withMime('application/pdf'),
        ];
    }
}
