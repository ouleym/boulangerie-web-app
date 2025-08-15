<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('livreur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('statut', ['assignee', 'en_route', 'livree', 'echec'])->default('assignee');
            $table->timestamp('heure_depart')->nullable();
            $table->timestamp('heure_livraison')->nullable();
            $table->text('commentaires')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};
