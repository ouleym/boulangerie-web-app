<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payements', function (Blueprint $table) {
            // Supprimer la clé étrangère si elle existe
            $table->dropForeign(['commande_id']);

            // Renommer la colonne
            $table->renameColumn('commande_id', 'user_id');

            // Ajouter la nouvelle clé étrangère vers users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payements', function (Blueprint $table) {
            // Supprimer la clé étrangère
            $table->dropForeign(['user_id']);

            // Renommer la colonne en sens inverse
            $table->renameColumn('user_id', 'commande_id');

            // Remettre l'ancienne clé étrangère si nécessaire
            // $table->foreign('commande_id')->references('id')->on('commandes');
        });
    }
};
