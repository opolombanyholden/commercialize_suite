<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== PERMISSIONS =====
        $permissions = [
            // Entreprises
            'companies.view' => 'Voir les entreprises',
            'companies.create' => 'Créer des entreprises',
            'companies.edit' => 'Modifier les entreprises',
            'companies.delete' => 'Supprimer les entreprises',

            // Sites
            'sites.view' => 'Voir les sites',
            'sites.create' => 'Créer des sites',
            'sites.edit' => 'Modifier les sites',
            'sites.delete' => 'Supprimer les sites',
            'sites.transfer_stock' => 'Transférer du stock entre sites',

            // Utilisateurs
            'users.view' => 'Voir les utilisateurs',
            'users.create' => 'Créer des utilisateurs',
            'users.edit' => 'Modifier les utilisateurs',
            'users.delete' => 'Supprimer les utilisateurs',
            'users.impersonate' => 'Se connecter en tant qu\'utilisateur',

            // Rôles et permissions
            'roles.view' => 'Voir les rôles',
            'roles.create' => 'Créer des rôles',
            'roles.edit' => 'Modifier les rôles',
            'roles.delete' => 'Supprimer les rôles',
            'roles.assign' => 'Assigner des rôles',

            // Produits
            'products.view' => 'Voir les produits',
            'products.create' => 'Créer des produits',
            'products.edit' => 'Modifier les produits',
            'products.delete' => 'Supprimer les produits',
            'products.publish_online' => 'Publier en ligne',
            'products.manage_variants' => 'Gérer les variantes',

            // Catégories
            'categories.view' => 'Voir les catégories',
            'categories.manage' => 'Gérer les catégories',

            // Clients
            'clients.view' => 'Voir les clients',
            'clients.create' => 'Créer des clients',
            'clients.edit' => 'Modifier les clients',
            'clients.delete' => 'Supprimer les clients',

            // Devis
            'quotes.view' => 'Voir les devis',
            'quotes.create' => 'Créer des devis',
            'quotes.edit' => 'Modifier les devis',
            'quotes.delete' => 'Supprimer les devis',
            'quotes.send' => 'Envoyer les devis',
            'quotes.convert' => 'Convertir en facture',

            // Factures
            'invoices.view' => 'Voir les factures',
            'invoices.create' => 'Créer des factures',
            'invoices.edit' => 'Modifier les factures',
            'invoices.delete' => 'Supprimer les factures',
            'invoices.send' => 'Envoyer les factures',
            'invoices.mark_paid' => 'Marquer comme payée',

            // Paiements
            'payments.view' => 'Voir les paiements',
            'payments.create' => 'Enregistrer des paiements',
            'payments.edit' => 'Modifier les paiements',
            'payments.delete' => 'Supprimer les paiements',

            // Bons de livraison
            'deliveries.view' => 'Voir les bons de livraison',
            'deliveries.create' => 'Créer des bons',
            'deliveries.edit' => 'Modifier des bons',
            'deliveries.delete' => 'Supprimer des bons',

            // Retours clients
            'returns.view' => 'Voir les retours clients',
            'returns.create' => 'Créer des retours',
            'returns.edit' => 'Gérer les retours (réception, résolution)',
            'returns.delete' => 'Supprimer des retours',

            // Stocks
            'inventory.view' => 'Voir les stocks',
            'inventory.adjust' => 'Ajuster les stocks',
            'inventory.transfer' => 'Transférer le stock',
            'inventory.receive' => 'Recevoir du stock',

            // E-commerce
            'ecommerce.manage_store' => 'Gérer la boutique',
            'ecommerce.view_orders' => 'Voir les commandes',
            'ecommerce.process_orders' => 'Traiter les commandes',
            'ecommerce.manage_shipping' => 'Gérer les expéditions',

            // Taxes
            'taxes.view' => 'Voir les taxes',
            'taxes.manage' => 'Gérer les taxes',

            // Rapports
            'reports.view' => 'Voir les rapports',
            'reports.export' => 'Exporter les rapports',
            'reports.financial' => 'Rapports financiers',
            'reports.inventory' => 'Rapports de stock',
            'reports.sales' => 'Rapports de ventes',

            // Paramètres
            'settings.view' => 'Voir les paramètres',
            'settings.edit' => 'Modifier les paramètres',
            'settings.company' => 'Paramètres entreprise',
            'settings.integrations' => 'Gérer les intégrations',

            // Audit
            'audit.view' => 'Voir les logs d\'audit',
        ];

        foreach ($permissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // ===== RÔLES =====
        $roles = [
            'super_admin' => [
                'description' => 'Administrateur système - Accès total',
                'permissions' => ['*'], // Toutes les permissions
            ],
            'company_admin' => [
                'description' => 'Administrateur entreprise',
                'permissions' => [
                    'companies.view', 'companies.edit',
                    'sites.*',
                    'users.*',
                    'roles.view', 'roles.assign',
                    'products.*',
                    'categories.*',
                    'clients.*',
                    'quotes.*',
                    'invoices.*',
                    'payments.*',
                    'deliveries.*',
                    'returns.*',
                    'inventory.*',
                    'ecommerce.*',
                    'taxes.*',
                    'reports.*',
                    'settings.*',
                    'audit.view',
                ],
            ],
            'site_manager' => [
                'description' => 'Gestionnaire de site',
                'permissions' => [
                    'sites.view',
                    'users.view', 'users.create', 'users.edit',
                    'products.*',
                    'categories.*',
                    'clients.*',
                    'quotes.*',
                    'invoices.*',
                    'payments.*',
                    'deliveries.*',
                    'returns.*',
                    'inventory.*',
                    'ecommerce.view_orders', 'ecommerce.process_orders',
                    'taxes.view',
                    'reports.view', 'reports.export',
                ],
            ],
            'accountant' => [
                'description' => 'Comptable',
                'permissions' => [
                    'clients.view',
                    'quotes.view',
                    'invoices.*',
                    'payments.*',
                    'deliveries.view',
                    'taxes.view',
                    'reports.view', 'reports.financial', 'reports.export',
                ],
            ],
            'sales_manager' => [
                'description' => 'Responsable commercial',
                'permissions' => [
                    'products.view',
                    'clients.*',
                    'quotes.*',
                    'invoices.*',
                    'payments.view',
                    'deliveries.*',
                    'reports.view', 'reports.sales', 'reports.export',
                ],
            ],
            'salesperson' => [
                'description' => 'Commercial',
                'permissions' => [
                    'products.view',
                    'clients.view', 'clients.create',
                    'quotes.*',
                    'invoices.view', 'invoices.create',
                    'deliveries.view',
                ],
            ],
            'warehouse_manager' => [
                'description' => 'Gestionnaire d\'entrepôt',
                'permissions' => [
                    'products.view',
                    'inventory.*',
                    'deliveries.*',
                    'reports.inventory',
                ],
            ],
            'viewer' => [
                'description' => 'Lecteur seul',
                'permissions' => [
                    'products.view',
                    'clients.view',
                    'quotes.view',
                    'invoices.view',
                    'deliveries.view',
                    'inventory.view',
                ],
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            // Assigner les permissions
            if ($roleData['permissions'] === ['*']) {
                // Super admin - toutes les permissions
                $role->givePermissionTo(Permission::all());
            } else {
                foreach ($roleData['permissions'] as $permission) {
                    if (str_ends_with($permission, '.*')) {
                        // Wildcard - toutes les permissions du groupe
                        $prefix = str_replace('.*', '', $permission);
                        $groupPermissions = Permission::where('name', 'like', $prefix . '.%')->get();
                        $role->givePermissionTo($groupPermissions);
                    } else {
                        // Permission spécifique
                        if (Permission::where('name', $permission)->exists()) {
                            $role->givePermissionTo($permission);
                        }
                    }
                }
            }
        }

        $this->command->info('✅ Rôles et permissions créés avec succès!');
        $this->command->info('   - ' . Permission::count() . ' permissions');
        $this->command->info('   - ' . Role::count() . ' rôles');
    }
}
