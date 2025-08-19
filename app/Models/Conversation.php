<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
<<<<<<< HEAD
    /** @use HasFactory<\Database\Factories\ConversationFactory> */
    use HasFactory;
=======
    use HasFactory;

    protected $fillable = ['user1_id', 'user2_id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
}
