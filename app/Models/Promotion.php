<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = [
        'nom', 'description', 'type', 'valeur', 'date_debut', 'date_fin'
    ];

    public function produits()
    {
        return $this->belongsToMany(Produit::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
