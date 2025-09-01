<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Créer les rôles si pas déjà existants
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $employeRole = Role::firstOrCreate(['name' => 'Employe']);
        $clientRole = Role::firstOrCreate(['name' => 'Client']);

        // ✅ Admins
        $admin1 = User::firstOrCreate(
            ['email' => 'admin1@example.com'],
            [
                'nom' => 'Diop',
                'prenom' => 'Moussa',
                'telephone' => '771112233',
                'adresse' => 'Dakar Plateau',
                'ville' => 'Dakar',
                'password' => Hash::make('password123')
            ]
        );
        $admin1->assignRole($adminRole);

        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@example.com'],
            [
                'nom' => 'Johnson',
                'prenom' => 'Emily',
                'telephone' => '772223344',
                'adresse' => 'New York City',
                'ville' => 'New York',
                'password' => Hash::make('password123')
            ]
        );
        $admin2->assignRole($adminRole);

        // ✅ Employés (5)
        $employes = [
            ['prenom' => 'Ibrahima', 'nom' => 'Ba', 'email' => 'ibrahima.ba@example.com', 'telephone' => '773334455', 'adresse' => 'Pikine', 'ville' => 'Dakar'],
            ['prenom' => 'Awa', 'nom' => 'Ndiaye', 'email' => 'awa.ndiaye@example.com', 'telephone' => '774445566', 'adresse' => 'Mermoz', 'ville' => 'Dakar'],
            ['prenom' => 'Cheikh', 'nom' => 'Fall', 'email' => 'cheikh.fall@example.com', 'telephone' => '775556677', 'adresse' => 'Ouakam', 'ville' => 'Dakar'],
            ['prenom' => 'Michael', 'nom' => 'Smith', 'email' => 'michael.smith@example.com', 'telephone' => '776667788', 'adresse' => 'Chicago', 'ville' => 'Chicago'],
            ['prenom' => 'Sarah', 'nom' => 'Brown', 'email' => 'sarah.brown@example.com', 'telephone' => '777778899', 'adresse' => 'Los Angeles', 'ville' => 'Los Angeles'],
        ];

        foreach ($employes as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'telephone' => $data['telephone'],
                    'adresse' => $data['adresse'],
                    'ville' => $data['ville'],
                    'password' => Hash::make('password123')
                ]
            );
            $user->assignRole($employeRole);
        }

        // ✅ Les autres utilisateurs (créés via l'inscription) auront automatiquement le rôle "Client"
        User::created(function ($user) use ($clientRole) {
            if (!$user->hasAnyRole(['Admin', 'Employe'])) {
                $user->assignRole($clientRole);
            }
        });
    }
}
