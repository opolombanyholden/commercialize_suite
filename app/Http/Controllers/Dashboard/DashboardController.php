<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord principal
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Période par défaut : ce mois
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Statistiques générales
        $stats = $this->getGeneralStats($companyId, $startDate, $endDate);

        // Graphique des ventes (6 derniers mois)
        $salesChart = $this->getSalesChart($companyId);

        // Dernières factures
        $latestInvoices = Invoice::where('company_id', $companyId)
            ->with('client')
            ->latest()
            ->take(5)
            ->get();

        // Derniers devis
        $latestQuotes = Quote::where('company_id', $companyId)
            ->with('client')
            ->latest()
            ->take(5)
            ->get();

        // Factures en retard
        $overdueInvoices = Invoice::where('company_id', $companyId)
            ->overdue()
            ->with('client')
            ->take(5)
            ->get();

        // Produits en rupture de stock (si feature disponible)
        $lowStockProducts = collect();
        if ($user->hasFeature('inventory')) {
            $lowStockProducts = Product::where('company_id', $companyId)
                ->lowStock()
                ->take(5)
                ->get();
        }

        // Top clients
        $topClients = Client::where('company_id', $companyId)
            ->topSpenders(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'salesChart',
            'latestInvoices',
            'latestQuotes',
            'overdueInvoices',
            'lowStockProducts',
            'topClients'
        ));
    }

    /**
     * Obtenir les statistiques générales
     */
    protected function getGeneralStats(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        // Chiffre d'affaires du mois
        $monthlyRevenue = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        // Chiffre d'affaires du mois précédent (pour comparaison)
        $previousMonthRevenue = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [
                $startDate->copy()->subMonth(),
                $endDate->copy()->subMonth()
            ])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $revenueChange = $previousMonthRevenue > 0
            ? round((($monthlyRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;

        // Nombre de factures ce mois
        $invoicesCount = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->count();

        // Montant en attente de paiement
        $pendingAmount = Invoice::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', '!=', 'cancelled')
            ->sum('balance');

        // Nombre de clients actifs
        $activeClients = Client::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        // Nombre de produits
        $productsCount = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        // Devis en attente
        $pendingQuotes = Quote::where('company_id', $companyId)
            ->whereIn('status', ['draft', 'sent'])
            ->count();

        // Taux de conversion devis -> facture
        $totalQuotes = Quote::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate->copy()->subMonths(3), $endDate])
            ->count();
        
        $convertedQuotes = Quote::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate->copy()->subMonths(3), $endDate])
            ->whereNotNull('converted_to_invoice_id')
            ->count();

        $conversionRate = $totalQuotes > 0 
            ? round(($convertedQuotes / $totalQuotes) * 100, 1) 
            : 0;

        return [
            'monthly_revenue' => $monthlyRevenue,
            'revenue_change' => $revenueChange,
            'invoices_count' => $invoicesCount,
            'pending_amount' => $pendingAmount,
            'active_clients' => $activeClients,
            'products_count' => $productsCount,
            'pending_quotes' => $pendingQuotes,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Obtenir les données pour le graphique des ventes
     */
    protected function getSalesChart(int $companyId): array
    {
        $months = collect();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push([
                'month' => $date->translatedFormat('M Y'),
                'revenue' => Invoice::where('company_id', $companyId)
                    ->whereYear('invoice_date', $date->year)
                    ->whereMonth('invoice_date', $date->month)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
            ]);
        }

        return [
            'labels' => $months->pluck('month')->toArray(),
            'data' => $months->pluck('revenue')->toArray(),
        ];
    }
}
