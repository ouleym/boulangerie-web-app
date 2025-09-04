<?php

// database/seeders/RolePermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Réinitialiser le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        $permissions = [
            // Gestion des produits
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Gestion des commandes
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'manage orders', // permission globale pour gérer les commandes

            // Gestion des livraisons
            'view deliveries',
            'edit deliveries',
            'manage deliveries',

            // Gestion des factures
            'view invoices',
            'send invoices',
            'download invoices',

            // Gestion des utilisateurs
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Gestion des promotions
            'view promotions',
            'create promotions',
            'edit promotions',
            'delete promotions',

            // Statistiques et rapports
            'view statistics',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Créer le rôle Admin avec toutes les permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Créer le rôle Employé avec permissions limitées
        $employeRole = Role::create(['name' => 'Employe']);
        $employeRole->givePermissionTo([
            'view products',
            'view orders',
            'edit orders',
            'manage orders',
            'view deliveries',
            'edit deliveries',
            'manage deliveries',
            'view invoices',
            'send invoices',
            'download invoices',
            'view users', // uniquement consultation
        ]);

        // Créer le rôle Client avec permissions de base
        $clientRole = Role::create(['name' => 'Client']);
        $clientRole->givePermissionTo([
            'view products',
            'create orders', // peut passer des commandes
            'view orders', // peut voir ses propres commandes
            'download invoices', // peut télécharger ses factures
        ]);
    }
}
