<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Commande;
use App\Models\Livraison;
use App\Notifications\LivraisonNotification;
use App\Mail\FactureEnvoiMail;
use Illuminate\Support\Facades\Mail;

class EnvoyerNotificationLivraison implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $commande;
    protected $livraison;
    protected $ancienStatut;
    protected $nouveauStatut;
    protected $envoyerFacture;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $user,
        Commande $commande,
        Livraison $livraison,
        $ancienStatut,
        $nouveauStatut,
        $envoyerFacture = false
    ) {
        $this->user = $user;
        $this->commande = $commande;
        $this->livraison = $livraison;
        $this->ancienStatut = $ancienStatut;
        $this->nouveauStatut = $nouveauStatut;
        $this->envoyerFacture = $envoyerFacture;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Envoyer la notification de changement de statut
            $this->user->notify(new LivraisonNotification(
                $this->commande,
                $this->livraison,
                $this->ancienStatut,
                $this->nouveauStatut
            ));

            // Envoyer la facture si la livraison est terminée
            if ($this->envoyerFacture && $this->nouveauStatut === 'livree') {
                Mail::to($this->user->email)->send(new FactureEnvoiMail(
                    $this->commande,
                    $this->user
                ));
            }

        } catch (\Exception $e) {
            \Log::error('Erreur job notification livraison: ' . $e->getMessage(), [
                'user_id' => $this->user->id,
                'commande_id' => $this->commande->id,
                'livraison_id' => $this->livraison->id,
                'ancien_statut' => $this->ancienStatut,
                'nouveau_statut' => $this->nouveauStatut
            ]);

            // Relancer le job après 5 minutes (max 3 tentatives)
            $this->release(300);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Job notification livraison échoué définitivement: ' . $exception->getMessage(), [
            'user_id' => $this->user->id,
            'commande_id' => $this->commande->id,
            'livraison_id' => $this->livraison->id
        ]);
    }
}
