<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\CategorieFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = ['nom'];

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
