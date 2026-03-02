<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔═══════════════════════════════════════════════╗');
        $this->command->info('║     CommercialiZe Suite - Database Seeder     ║');
        $this->command->info('╚═══════════════════════════════════════════════╝');
        $this->command->info('');

        // 1. Rôles et Permissions (toujours en premier)
        $this->call(RolePermissionSeeder::class);

        // 2. Données de démonstration (inclut TaxSeeder)
        $this->call(DemoDataSeeder::class);

        $this->command->info('');
        $this->command->info('✅ Base de données initialisée avec succès!');
        $this->command->info('');
    }
}
