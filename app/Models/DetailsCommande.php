<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailsCommande extends Model
{
    /** @use HasFactory<\Database\Factories\DetailsCommandeFactory> */
    use HasFactory;
=======
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailsCommande extends Model
{
    use HasFactory;

    protected $fillable = ['commande_id', 'produit_id', 'quantite'];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
