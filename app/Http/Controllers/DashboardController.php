<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Statistiques générales
        $stats = [
            'total_clients' => Client::where('company_id', $companyId)->count(),
            'total_products' => Product::where('company_id', $companyId)->where('is_active', true)->count(),
            'total_quotes' => Quote::where('company_id', $companyId)->count(),
            'total_invoices' => Invoice::where('company_id', $companyId)->count(),
        ];

        // Chiffre d'affaires du mois
        $stats['monthly_revenue'] = Invoice::where('company_id', $companyId)
            ->where('payment_status', 'paid')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('total_amount');

        // Factures en attente
        $stats['pending_invoices'] = Invoice::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('balance');

        // Factures en retard
        $stats['overdue_invoices'] = Invoice::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('due_date', '<', now())
            ->count();

        // Produits en rupture de stock (si feature activée)
        if ($user->hasFeature('inventory')) {
            $stats['low_stock_products'] = Product::where('company_id', $companyId)
                ->where('track_inventory', true)
                ->whereNotNull('stock_alert_threshold')
                ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
                ->count();
        }

        // Dernières factures
        $recentInvoices = Invoice::where('company_id', $companyId)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Derniers devis
        $recentQuotes = Quote::where('company_id', $companyId)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chiffre d'affaires des 6 derniers mois (pour graphique)
        $revenueByMonth = Invoice::where('company_id', $companyId)
            ->where('payment_status', 'paid')
            ->where('invoice_date', '>=', now()->subMonths(6))
            ->select(
                DB::raw('YEAR(invoice_date) as year'),
                DB::raw('MONTH(invoice_date) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'recentInvoices',
            'recentQuotes',
            'revenueByMonth'
        ));
    }
}
