<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Site;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏢 Création de l\'entreprise de démonstration...');

        // ===== ENTREPRISE =====
        $company = Company::create([
            'name' => 'CommercialiZe Demo',
            'legal_name' => 'CommercialiZe Demo SARL',
            'slug' => 'commercialize-demo',
            'email' => 'contact@demo.commercialize.ga',
            'phone' => '+241 01 23 45 67',
            'address' => '123 Boulevard Triomphal',
            'city' => 'Libreville',
            'postal_code' => 'BP 1234',
            'country' => 'GA',
            'currency' => 'XAF',
            'timezone' => 'Africa/Libreville',
            'tax_id' => 'GA123456789',
            'is_active' => true,
            'settings' => [
                'invoice_prefix' => 'F',
                'quote_prefix' => 'D',
                'payment_terms' => 30,
            ],
        ]);

        $this->command->info('   ✅ Entreprise créée: ' . $company->name);

        // ===== SITE PRINCIPAL =====
        $site = Site::create([
            'company_id' => $company->id,
            'name' => 'Siège Social',
            'code' => 'HQ',
            'is_headquarters' => true,
            'is_warehouse' => true,
            'is_store' => true,
            'email' => 'siege@demo.commercialize.ga',
            'phone' => '+241 01 23 45 67',
            'address' => '123 Boulevard Triomphal',
            'city' => 'Libreville',
            'country' => 'GA',
            'is_active' => true,
        ]);

        $this->command->info('   ✅ Site créé: ' . $site->name);

        // ===== UTILISATEUR ADMIN =====
        $admin = User::create([
            'name' => 'Administrateur Demo',
            'email' => 'admin@demo.commercialize.ga',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'version' => 'enterprise',
            'phone' => '+241 01 23 45 67',
            'job_title' => 'Directeur Général',
            'language' => 'fr',
            'timezone' => 'Africa/Libreville',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assigner le rôle admin
        $admin->assignRole('company_admin');

        // Assigner l'accès au site
        $admin->sites()->attach($site->id, ['is_primary' => true]);

        $this->command->info('   ✅ Utilisateur admin créé: ' . $admin->email);

        // ===== UTILISATEUR COMMERCIAL =====
        $sales = User::create([
            'name' => 'Jean Commercial',
            'email' => 'commercial@demo.commercialize.ga',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'version' => 'enterprise',
            'phone' => '+241 01 98 76 54',
            'job_title' => 'Commercial',
            'language' => 'fr',
            'timezone' => 'Africa/Libreville',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $sales->assignRole('salesperson');
        $sales->sites()->attach($site->id, ['is_primary' => true]);

        $this->command->info('   ✅ Utilisateur commercial créé: ' . $sales->email);

        // ===== TAXES =====
        TaxSeeder::createDefaultTaxes($company);
        $this->command->info('   ✅ Taxes créées');

        // ===== CATÉGORIES =====
        $categories = [
            ['name' => 'Informatique', 'slug' => 'informatique'],
            ['name' => 'Bureautique', 'slug' => 'bureautique'],
            ['name' => 'Services', 'slug' => 'services'],
            ['name' => 'Consommables', 'slug' => 'consommables'],
        ];

        foreach ($categories as $catData) {
            Category::create([
                'company_id' => $company->id,
                'name' => $catData['name'],
                'slug' => $catData['slug'],
                'is_active' => true,
                'is_visible_online' => true,
            ]);
        }

        $this->command->info('   ✅ ' . count($categories) . ' catégories créées');

        // ===== PRODUITS =====
        $informatique = Category::where('slug', 'informatique')->first();
        $services = Category::where('slug', 'services')->first();

        $products = [
            [
                'name' => 'Ordinateur Portable HP',
                'slug' => 'ordinateur-portable-hp',
                'type' => 'product',
                'category_id' => $informatique->id,
                'price' => 450000,
                'cost_price' => 350000,
                'short_description' => 'Ordinateur portable HP 15 pouces',
                'track_inventory' => true,
                'stock_quantity' => 10,
                'stock_alert_threshold' => 2,
            ],
            [
                'name' => 'Imprimante Laser Canon',
                'slug' => 'imprimante-laser-canon',
                'type' => 'product',
                'category_id' => $informatique->id,
                'price' => 185000,
                'cost_price' => 140000,
                'short_description' => 'Imprimante laser monochrome',
                'track_inventory' => true,
                'stock_quantity' => 5,
                'stock_alert_threshold' => 1,
            ],
            [
                'name' => 'Installation Réseau',
                'slug' => 'installation-reseau',
                'type' => 'service',
                'category_id' => $services->id,
                'price' => 75000,
                'short_description' => 'Installation et configuration réseau',
                'track_inventory' => false,
            ],
            [
                'name' => 'Maintenance Informatique',
                'slug' => 'maintenance-informatique',
                'type' => 'service',
                'category_id' => $services->id,
                'price' => 50000,
                'short_description' => 'Maintenance mensuelle parc informatique',
                'track_inventory' => false,
            ],
        ];

        foreach ($products as $prodData) {
            Product::create(array_merge($prodData, [
                'company_id' => $company->id,
                'is_active' => true,
                'is_published_online' => true,
            ]));
        }

        $this->command->info('   ✅ ' . count($products) . ' produits créés');

        // ===== CLIENTS =====
        $clients = [
            [
                'type' => 'business',
                'name' => 'Marie Directrice',
                'company_name' => 'Société ABC SARL',
                'email' => 'contact@abc-sarl.ga',
                'phone' => '+241 01 11 22 33',
                'address' => '456 Avenue de la Liberté',
                'city' => 'Libreville',
                'country' => 'GA',
            ],
            [
                'type' => 'business',
                'name' => 'Pierre Gérant',
                'company_name' => 'Entreprise XYZ',
                'email' => 'info@xyz.ga',
                'phone' => '+241 01 44 55 66',
                'address' => '789 Rue du Commerce',
                'city' => 'Port-Gentil',
                'country' => 'GA',
            ],
            [
                'type' => 'individual',
                'name' => 'Sophie Particulier',
                'email' => 'sophie.particulier@email.ga',
                'phone' => '+241 06 77 88 99',
                'address' => '12 Quartier Louis',
                'city' => 'Libreville',
                'country' => 'GA',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create(array_merge($clientData, [
                'company_id' => $company->id,
                'is_active' => true,
            ]));
        }

        $this->command->info('   ✅ ' . count($clients) . ' clients créés');

        // ===== RÉSUMÉ =====
        $this->command->newLine();
        $this->command->info('🎉 Données de démonstration créées avec succès!');
        $this->command->newLine();
        $this->command->table(
            ['Élément', 'Valeur'],
            [
                ['Entreprise', $company->name],
                ['Email Admin', 'admin@demo.commercialize.ga'],
                ['Mot de passe', 'password'],
                ['Email Commercial', 'commercial@demo.commercialize.ga'],
                ['Catégories', count($categories)],
                ['Produits', count($products)],
                ['Clients', count($clients)],
            ]
        );
    }
}
