<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payement extends Model
{
    const STATUT_EN_ATTENTE = "en_attente";
    const STATUT_SUCCESS = "success";
    const STATUT_FAILED = "failed";

    public static function getStatut()
    {
        return [
            self::STATUT_EN_ATTENTE,
            self::STATUT_SUCCESS,
            self::STATUT_FAILED,
        ];
    }

    protected $fillable = [
        'user_id',        // Ajouté user_id pour correspondre à la migration
        'commande_id',    // Optionnel si vous avez des commandes
        'montant',
        'methode',
        'devise',
        'statut',
        'transaction_ref',
        'date_paiement',
        'status'          // Garde aussi status pour compatibilité
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function commande()
    {
        return $this->belongsTo(\App\Models\Commande::class, 'commande_id');
    }
}
