<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les rôles s'ils n'existent pas déjà
        $this->createRoles();

        // Créer les permissions de base
        $this->createPermissions();

        // Assigner les permissions aux rôles
        $this->assignPermissionsToRoles();

        // Créer les utilisateurs administrateurs
        $this->createAdminUsers();
    }

    /**
     * Créer les rôles
     */
    private function createRoles(): void
    {
        $roles = ['Admin', 'Employe', 'Client'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            $this->command->info("Rôle '{$roleName}' créé ou existe déjà.");
        }
    }

    /**
     * Créer les permissions de base
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Gestion des utilisateurs
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Gestion des produits
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Gestion des catégories
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Gestion des commandes
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',
            'manage-order-status',

            // Gestion des promotions
            'view-promotions',
            'create-promotions',
            'edit-promotions',
            'delete-promotions',

            // Gestion des livraisons
            'view-deliveries',
            'manage-deliveries',

            // Gestion des factures
            'view-invoices',
            'create-invoices',
            'edit-invoices',

            // Gestion des conversations/support
            'view-conversations',
            'create-conversations',
            'manage-conversations',

            // Dashboard et statistiques
            'view-admin-dashboard',
            'view-employee-dashboard',
            'view-client-dashboard',
            'view-statistics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info('Permissions créées.');
    }

    /**
     * Assigner les permissions aux rôles
     */
    private function assignPermissionsToRoles(): void
    {
        $adminRole = Role::findByName('Admin');
        $employeRole = Role::findByName('Employe');
        $clientRole = Role::findByName('Client');

        // Admin : toutes les permissions
        $adminRole->syncPermissions(Permission::all());

        // Employé : permissions limitées
        $employePermissions = [
            'view-products',
            'edit-products',
            'view-categories',
            'view-orders',
            'edit-orders',
            'manage-order-status',
            'view-deliveries',
            'manage-deliveries',
            'view-invoices',
            'view-conversations',
            'manage-conversations',
            'view-employee-dashboard',
        ];
        $employeRole->syncPermissions($employePermissions);

        // Client : permissions de base
        $clientPermissions = [
            'view-products',
            'view-categories',
            'create-orders',
            'view-orders',
            'view-promotions',
            'create-conversations',
            'view-conversations',
            'view-client-dashboard',
        ];
        $clientRole->syncPermissions($clientPermissions);

        $this->command->info('Permissions assignées aux rôles.');
    }

    /**
     * Créer les utilisateurs administrateurs
     */
    private function createAdminUsers(): void
    {
        $adminRole = Role::findByName('Admin');

        $admins = [
            [
                'nom' => 'Ba',
                'prenom' => 'Fatime',
                'email' => 'fatime01@gmail.com',
                'password' => 'Fazilla#123',
                'telephone' => '+221 77 123 45 67',
                'adresse' => 'Dakar, Sénégal',
                'ville' => 'Dakar'
            ],
            [
                'nom' => 'Diallo',
                'prenom' => 'Ouleymatou',
                'email' => 'ouleym01@gmail.com',
                'password' => 'Ouleymb#123',
                'telephone' => '+221 76 987 65 43',
                'adresse' => 'Pikine, Sénégal',
                'ville' => 'Pikine'
            ]
        ];

        foreach ($admins as $adminData) {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('email', $adminData['email'])->first();

            if ($existingUser) {
                $this->command->warn("L'utilisateur {$adminData['email']} existe déjà.");

                // Assurer que l'utilisateur a le rôle Admin
                if (!$existingUser->hasRole('Admin')) {
                    $existingUser->assignRole($adminRole);
                    $this->command->info("Rôle Admin assigné à {$adminData['email']}.");
                }
                continue;
            }

            // Créer le nouvel utilisateur
            $user = User::create([
                'nom' => $adminData['nom'],
                'prenom' => $adminData['prenom'],
                'email' => $adminData['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($adminData['password']),
                'telephone' => $adminData['telephone'],
                'adresse' => $adminData['adresse'],
                'ville' => $adminData['ville'],
            ]);

            // Assigner le rôle Admin
            $user->assignRole($adminRole);

            $this->command->info("Utilisateur Admin créé : {$adminData['email']}");
        }
    }
}
