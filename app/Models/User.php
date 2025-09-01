<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'adresse',
        'ville',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * JWT : Identifiant utilisateur
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT : Claims personnalisés (ajout des rôles Spatie)
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'roles' => $this->getRoleNames() // ✅ Les rôles seront inclus dans le token
        ];
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
