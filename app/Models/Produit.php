<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{

    use HasFactory;

    protected $fillable = [
        'nom', 'description', 'prix', 'stock', 'photo', 'allergenes', 'categorie_id'
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'details_commandes')->withPivot('quantite');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class);
    }
}
