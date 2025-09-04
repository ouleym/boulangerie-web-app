<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Commande;
use App\Models\Livraison;

class LivraisonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $commande;
    protected $livraison;
    protected $ancienStatut;
    protected $nouveauStatut;

    public function __construct(Commande $commande, Livraison $livraison, $ancienStatut, $nouveauStatut)
    {
        $this->commande = $commande;
        $this->livraison = $livraison;
        $this->ancienStatut = $ancienStatut;
        $this->nouveauStatut = $nouveauStatut;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $sujet = $this->getSujetParStatut();

        return (new MailMessage)
            ->subject($sujet)
            ->view('emails.livraison-status', [
                'user' => $notifiable,
                'commande' => $this->commande,
                'livraison' => $this->livraison,
                'ancienStatut' => $this->ancienStatut,
                'nouveauStatut' => $this->nouveauStatut
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'commande_id' => $this->commande->id,
            'livraison_id' => $this->livraison->id,
            'ancien_statut' => $this->ancienStatut,
            'nouveau_statut' => $this->nouveauStatut,
            'message' => "Votre commande #{$this->commande->id} est maintenant {$this->nouveauStatut}"
        ];
    }

    private function getSujetParStatut()
    {
        $sujets = [
            'en_attente' => 'Votre commande a été reçue',
            'en_preparation' => 'Votre commande est en préparation',
            'prete' => 'Votre commande est prête',
            'en_livraison' => 'Votre commande est en cours de livraison',
            'livree' => 'Votre commande a été livrée',
            'annulee' => 'Votre commande a été annulée'
        ];

        return $sujets[$this->nouveauStatut] ?? 'Mise à jour de votre commande';
    }
}
