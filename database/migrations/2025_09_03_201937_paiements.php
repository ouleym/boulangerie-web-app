<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration.
     */
    public function up(): void
    {
        Schema::create('payements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')
                ->constrained()
                ->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('methode', 50);
            $table->string('devise')->default('XOF');
            $table->enum('statut', ['en_attente', 'valide', 'echoue', 'rembourse'])
                ->default('en_attente');
            $table->string('transaction_ref')->unique();
            $table->timestamp('date_paiement')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Annule la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('payements');
    }
};
