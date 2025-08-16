<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('montant_total', 10, 2);
            $table->decimal('montant_promotions', 10, 2)->default(0);
            $table->enum('statut', ['en_attente', 'confirmee', 'en_preparation', 'prete', 'en_livraison', 'livree', 'annulee'])->default('en_attente');
            $table->enum('mode_paiement', ['en_ligne', 'a_la_livraison']);
            $table->enum('statut_paiement', ['en_attente', 'paye', 'non_paye'])->default('en_attente');
            $table->text('adresse_livraison');
            $table->timestamp('date_souhaitee')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
