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
        'user_id',
        'commande_id',
        'montant',
        'methode',
        'devise',
        'statut',
        'transaction_ref',
        'payment_token',      // ✅ AJOUTÉ
        'cinetpay_data',      // ✅ AJOUTÉ
        'date_paiement',
        'status'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
        'cinetpay_data' => 'array'  // ✅ AJOUTÉ
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
