<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
=======
use App\Models\User;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\Promotion;

class DashboardController extends Controller
{
    /**
     * Retourner les statistiques principales pour l’admin/gérant.
     */
    public function stats()
    {
        $nbClients = User::where('role', 'client')->count();
        $nbEmployes = User::where('role', 'employe')->count();
        $nbProduits = Produit::count();
        $nbCommandes = Commande::count();
        $chiffreAffaires = Commande::where('statut', 'livree')->sum('total');
        $nbPromotions = Promotion::count();

        return response()->json([
            'clients' => $nbClients,
            'employes' => $nbEmployes,
            'produits' => $nbProduits,
            'commandes' => $nbCommandes,
            'chiffre_affaires' => $chiffreAffaires,
            'promotions' => $nbPromotions
        ]);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
