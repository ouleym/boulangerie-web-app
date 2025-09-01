<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder pour les rôles, permissions et utilisateurs admin
        $this->call([
            AdminUsersSeeder::class,
        ]);
        $this->call(UserSeeder::class);


        // Créer des utilisateurs de test (optionnel - décommentez si nécessaire)
        // User::factory(10)->create();

        // Utilisateur de test (décommentez si vous voulez le garder)
        /*
        User::factory()->create([
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
        ]);
        */
    }
}
