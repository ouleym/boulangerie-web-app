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
        Schema::table('users', function (Blueprint $table) {
            // Renommer la colonne name en nom
            $table->renameColumn('name', 'nom');

            // Ajouter les nouvelles colonnes
            $table->string('prenom')->after('nom');
            $table->string('telephone')->nullable()->after('email');
            $table->text('adresse')->nullable()->after('telephone');
            $table->string('ville')->nullable()->after('adresse');
            $table->string('code_postal')->nullable()->after('ville');
            $table->boolean('actif')->default(true)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'prenom',
                'telephone',
                'adresse',
                'ville',
                'code_postal',
                'actif'
            ]);

            // Remettre nom en name
            $table->renameColumn('nom', 'name');
        });
    }
};
