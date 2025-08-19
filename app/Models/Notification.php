<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = ['user_id', 'contenu', 'statut'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
