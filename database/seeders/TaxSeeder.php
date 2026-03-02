<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Taxes prédéfinies pour le Gabon
     */
    protected array $gabonTaxes = [
        [
            'name' => 'TPS Standard',
            'rate' => 18.00,
            'description' => 'Taxe sur les Prestations de Services - Taux standard',
            'apply_to' => 'all',
            'is_default' => true,
            'sort_order' => 1,
        ],
        [
            'name' => 'TPS Réduite',
            'rate' => 10.00,
            'description' => 'Taxe sur les Prestations de Services - Taux réduit',
            'apply_to' => 'all',
            'is_default' => false,
            'sort_order' => 2,
        ],
        [
            'name' => 'Exonéré',
            'rate' => 0.00,
            'description' => 'Produits et services exonérés de taxe',
            'apply_to' => 'all',
            'is_default' => false,
            'sort_order' => 3,
        ],
    ];

    public function run(): void
    {
        // Créer les taxes pour chaque entreprise existante
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('⚠️  Aucune entreprise trouvée. Créez d\'abord une entreprise.');
            return;
        }

        foreach ($companies as $company) {
            $this->createTaxesForCompany($company);
        }

        $this->command->info('✅ Taxes Gabon créées avec succès!');
    }

    /**
     * Créer les taxes pour une entreprise spécifique
     */
    public function createTaxesForCompany(Company $company): void
    {
        foreach ($this->gabonTaxes as $taxData) {
            Tax::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $taxData['name'],
                ],
                array_merge($taxData, [
                    'company_id' => $company->id,
                    'is_active' => true,
                ])
            );
        }
    }

    /**
     * Créer les taxes par défaut pour une nouvelle entreprise
     * (À appeler lors de la création d'une nouvelle entreprise)
     */
    public static function createDefaultTaxes(Company $company): void
    {
        $seeder = new self();
        $seeder->createTaxesForCompany($company);
    }
}
