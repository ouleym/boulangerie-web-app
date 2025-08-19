<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\CommandeFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = [
        'client_id', 'statut', 'mode_paiement', 'total'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'details_commandes')->withPivot('quantite');
    }

    public function livraison()
    {
        return $this->hasOne(Livraison::class);
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
