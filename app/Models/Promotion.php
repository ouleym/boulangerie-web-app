<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'description', 'type', 'valeur', 'date_debut', 'date_fin'
    ];

    public function produits()
    {
        return $this->belongsToMany(Produit::class);
    }
}
