<?php

namespace App\Observers;

use App\Models\Livraison;
use App\Models\User;
use App\Jobs\EnvoyerNotificationLivraison;

class LivraisonObserver
{
    public function updated(Livraison $livraison)
    {
        // Vérifier si le statut a changé
        if ($livraison->isDirty('statut')) {
            $ancienStatut = $livraison->getOriginal('statut');
            $nouveauStatut = $livraison->statut;

            // Récupérer la commande et l'utilisateur
            $commande = $livraison->commande;
            $user = User::find($commande->user_id);

            if ($user) {
                // Utiliser un job pour traiter les notifications de manière asynchrone
                $envoyerFacture = ($nouveauStatut === 'livree');

                EnvoyerNotificationLivraison::dispatch(
                    $user,
                    $commande,
                    $livraison,
                    $ancienStatut,
                    $nouveauStatut,
                    $envoyerFacture
                );
            }
        }
    }
}
