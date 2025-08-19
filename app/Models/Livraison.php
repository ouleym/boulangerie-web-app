<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\LivraisonFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = ['commande_id', 'employe_id', 'statut'];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function employe()
    {
        return $this->belongsTo(User::class, 'employe_id');
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
