<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'adresse',
        'ville',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation avec les commandes
     */
    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    /**
     * Vérifier si l'utilisateur est un client
     */
    public function isClient()
    {
        return $this->hasRole('Client');
    }

    /**
     * Vérifier si l'utilisateur est un employé
     */
    public function isEmploye()
    {
        return $this->hasRole('Employe');
    }

    /**
     * Vérifier si l'utilisateur est un admin
     */
    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }

    /**
     * Vérifier si l'utilisateur peut gérer les commandes
     */
    public function canManageOrders()
    {
        return $this->hasAnyRole(['Admin', 'Employe']);
    }

    /**
     * Scope pour les clients seulement
     */
    public function scopeClients($query)
    {
        return $query->role('Client');
    }

    /**
     * Scope pour les employés et admins
     */
    public function scopeStaff($query)
    {
        return $query->role(['Admin', 'Employe']);
    }
}
