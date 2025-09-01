<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
}
