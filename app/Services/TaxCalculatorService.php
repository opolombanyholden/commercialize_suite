<?php

namespace App\Services;

use App\Models\Tax;
use Illuminate\Support\Collection;

class TaxCalculatorService
{
    /**
     * Calculer les taxes pour une liste d'items
     *
     * @param array $items Liste des items avec 'type' et 'total'
     * @param array $taxes Liste des taxes à appliquer
     * @return array Résultat avec détail des taxes
     */
    public function calculate(array $items, array $taxes): array
    {
        $result = [
            'subtotal' => 0,
            'taxes' => [],
            'total_tax' => 0,
            'grand_total' => 0,
        ];

        // Calculer le sous-total
        $result['subtotal'] = collect($items)->sum('total');

        // Calculer les totaux par type
        $totalProducts = collect($items)->where('type', 'product')->sum('total');
        $totalServices = collect($items)->where('type', 'service')->sum('total');

        // Calculer chaque taxe
        foreach ($taxes as $tax) {
            $taxableBase = $this->calculateTaxableBase(
                $tax['apply_to'] ?? 'all',
                $result['subtotal'],
                $totalProducts,
                $totalServices
            );

            $taxAmount = round($taxableBase * (($tax['rate'] ?? 0) / 100), 2);

            $result['taxes'][] = [
                'tax_id' => $tax['id'] ?? null,
                'name' => $tax['name'] ?? 'Taxe',
                'rate' => $tax['rate'] ?? 0,
                'apply_to' => $tax['apply_to'] ?? 'all',
                'base' => $taxableBase,
                'amount' => $taxAmount,
            ];

            $result['total_tax'] += $taxAmount;
        }

        $result['grand_total'] = $result['subtotal'] + $result['total_tax'];

        return $result;
    }

    /**
     * Calculer la base imposable selon le type d'application
     */
    protected function calculateTaxableBase(
        string $applyTo,
        float $total,
        float $totalProducts,
        float $totalServices
    ): float {
        return match($applyTo) {
            'products' => $totalProducts,
            'services' => $totalServices,
            default => $total, // 'all'
        };
    }

    /**
     * Calculer les taxes à partir de modèles Tax
     */
    public function calculateFromModels(array $items, Collection $taxes): array
    {
        $taxArray = $taxes->map(fn($tax) => [
            'id' => $tax->id,
            'name' => $tax->name,
            'rate' => $tax->rate,
            'apply_to' => $tax->apply_to,
        ])->toArray();

        return $this->calculate($items, $taxArray);
    }

    /**
     * Obtenir le résumé des taxes pour affichage
     */
    public function getTaxSummary(array $calculatedTaxes): array
    {
        return collect($calculatedTaxes['taxes'])
            ->map(fn($tax) => [
                'label' => $tax['name'] . ' (' . $tax['rate'] . '%)',
                'base' => number_format($tax['base'], 0, ',', ' ') . ' FCFA',
                'amount' => number_format($tax['amount'], 0, ',', ' ') . ' FCFA',
            ])
            ->toArray();
    }
}
