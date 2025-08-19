<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = ['conversation_id', 'sender_id', 'contenu'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
