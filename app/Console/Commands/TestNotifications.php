<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Commande;
use App\Models\Livraison;
use App\Notifications\LivraisonNotification;
use App\Mail\FactureEnvoiMail;
use Illuminate\Support\Facades\Mail;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:notifications {--user-id=} {--commande-id=} {--type=status}';

    /**
     * The console command description.
     */
    protected $description = 'Tester les notifications de livraison et l\'envoi de factures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $commandeId = $this->option('commande-id');
        $type = $this->option('type');

        // Si pas d'IDs spécifiés, utiliser les premiers disponibles
        if (!$userId) {
            $user = User::role('Client')->first();
            if (!$user) {
                $this->error('Aucun utilisateur client trouvé');
                return 1;
            }
        } else {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Utilisateur avec ID {$userId} introuvable");
                return 1;
            }
        }

        if (!$commandeId) {
            $commande = Commande::where('user_id', $user->id)->first();
            if (!$commande) {
                $this->error('Aucune commande trouvée pour cet utilisateur');
                return 1;
            }
        } else {
            $commande = Commande::find($commandeId);
            if (!$commande) {
                $this->error("Commande avec ID {$commandeId} introuvable");
                return 1;
            }
        }

        $livraison = $commande->livraison;
        if (!$livraison) {
            $this->error('Aucune livraison trouvée pour cette commande');
            return 1;
        }

        $this->info("Test avec utilisateur: {$user->prenom} {$user->nom} ({$user->email})");
        $this->info("Commande: #{$commande->id}");
        $this->info("Statut actuel de livraison: {$livraison->statut}");

        switch ($type) {
            case 'status':
                $this->testNotificationStatut($user, $commande, $livraison);
                break;
            case 'facture':
                $this->testEnvoiFacture($user, $commande);
                break;
            case 'both':
                $this->testNotificationStatut($user, $commande, $livraison);
                $this->testEnvoiFacture($user, $commande);
                break;
            default:
                $this->error("Type de test invalide. Utilisez: status, facture, ou both");
                return 1;
        }

        return 0;
    }

    private function testNotificationStatut($user, $commande, $livraison)
    {
        $this->info("\n=== Test de notification de changement de statut ===");

        $ancienStatut = $livraison->statut;
        $nouveauStatut = $this->choice(
            'Quel nouveau statut tester ?',
            ['confirmee', 'en_preparation', 'prete', 'en_livraison', 'livree'],
            0
        );

        try {
            $user->notify(new LivraisonNotification(
                $commande,
                $livraison,
                $ancienStatut,
                $nouveauStatut
            ));

            $this->line("✅ Notification envoyée avec succès !");
            $this->line("   Ancien statut: {$ancienStatut}");
            $this->line("   Nouveau statut: {$nouveauStatut}");
            $this->line("   Email destinataire: {$user->email}");

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'envoi de la notification: " . $e->getMessage());
        }
    }

    private function testEnvoiFacture($user, $commande)
    {
        $this->info("\n=== Test d'envoi de facture ===");

        try {
            Mail::to($user->email)->send(new FactureEnvoiMail($commande, $user));

            $this->line("✅ Facture envoyée avec succès !");
            $this->line("   Commande: #{$commande->id}");
            $this->line("   Montant: " . number_format($commande->montant_total, 2, ',', ' ') . " €");
            $this->line("   Email destinataire: {$user->email}");

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'envoi de la facture: " . $e->getMessage());
        }
    }
}
