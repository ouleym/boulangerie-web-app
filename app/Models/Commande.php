<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
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

}
