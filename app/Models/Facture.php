<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\FactureFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = ['commande_id', 'montant', 'pdf_path'];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
