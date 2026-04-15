<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view')->only(['index']);
    }

    /**
     * Tableau de bord — Phase 1 : Vue d'ensemble + Trésorerie de base.
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        // Période par défaut : 12 derniers mois
        $startMonth = Carbon::now()->subMonths(11)->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd   = $now->copy()->subMonth()->endOfMonth();
        $yearStart  = $now->copy()->startOfYear();
        $prevYearStart = $now->copy()->subYear()->startOfYear();
        $prevYearEnd   = $now->copy()->subYear()->endOfYear();

        // Base : factures de l'entreprise (hors avoirs)
        $invoiceBase = Invoice::where('company_id', $companyId)
            ->where('type', '!=', 'credit_note');

        // ===== KPIs =====
        $caMonth      = (clone $invoiceBase)->whereBetween('invoice_date', [$monthStart, $monthEnd])->sum('total_amount');
        $caPrevMonth  = (clone $invoiceBase)->whereBetween('invoice_date', [$prevMonthStart, $prevMonthEnd])->sum('total_amount');
        $caYear       = (clone $invoiceBase)->whereBetween('invoice_date', [$yearStart, $now])->sum('total_amount');
        $caPrevYearSamePeriod = (clone $invoiceBase)
            ->whereBetween('invoice_date', [$prevYearStart, $now->copy()->subYear()])
            ->sum('total_amount');

        $invoicesIssuedMonth   = (clone $invoiceBase)->whereBetween('invoice_date', [$monthStart, $monthEnd])->count();
        $invoicesPaidMonth     = (clone $invoiceBase)->whereBetween('invoice_date', [$monthStart, $monthEnd])->where('payment_status', 'paid')->count();
        $invoicesOverdue       = (clone $invoiceBase)->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now->toDateString())
            ->count();
        $totalOverdueAmount    = (clone $invoiceBase)->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now->toDateString())
            ->sum('balance');

        $avgTicket = $invoicesIssuedMonth > 0 ? ($caMonth / $invoicesIssuedMonth) : 0;

        $variationMonth = $caPrevMonth > 0 ? (($caMonth - $caPrevMonth) / $caPrevMonth) * 100 : null;

        // ===== Série mensuelle (12 derniers mois) — facturé vs encaissé =====
        $months = [];
        $cursor = $startMonth->copy();
        while ($cursor <= $endMonth) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $billedByMonth = (clone $invoiceBase)
            ->whereBetween('invoice_date', [$startMonth, $endMonth])
            ->select(DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as ym"), DB::raw('SUM(total_amount) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $collectedByMonth = Payment::where('company_id', $companyId)
            ->where('is_confirmed', true)
            ->whereBetween('payment_date', [$startMonth, $endMonth])
            ->select(DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $billedSeries    = collect($months)->map(fn($m) => (float) ($billedByMonth[$m] ?? 0))->all();
        $collectedSeries = collect($months)->map(fn($m) => (float) ($collectedByMonth[$m] ?? 0))->all();
        $monthLabels     = collect($months)->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->locale('fr')->isoFormat('MMM YY'))->all();

        // ===== Balance âgée des créances =====
        $today = $now->copy()->startOfDay();
        $unpaid = (clone $invoiceBase)->whereIn('payment_status', ['unpaid', 'partial'])->get(['id', 'due_date', 'balance']);
        $aging = ['current' => 0, '0_30' => 0, '30_60' => 0, '60_90' => 0, 'over_90' => 0];
        foreach ($unpaid as $inv) {
            $balance = (float) $inv->balance;
            if (!$inv->due_date) {
                $aging['current'] += $balance;
                continue;
            }
            // Nombre de jours de retard (positif si en retard, 0 ou négatif si pas encore échu)
            $dueDate = $inv->due_date->copy()->startOfDay();
            $daysOverdue = (int) round($dueDate->diffInDays($today, false));

            if ($daysOverdue <= 0) {
                $aging['current'] += $balance;
            } elseif ($daysOverdue <= 30) {
                $aging['0_30'] += $balance;
            } elseif ($daysOverdue <= 60) {
                $aging['30_60'] += $balance;
            } elseif ($daysOverdue <= 90) {
                $aging['60_90'] += $balance;
            } else {
                $aging['over_90'] += $balance;
            }
        }

        // ===== Top 10 clients à relancer =====
        // Inclut TOUTES les factures non soldées (à échoir + en retard).
        // On groupe par client_id (plus fiable que client_name).
        $topOverdueClients = (clone $invoiceBase)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->select(
                DB::raw('COALESCE(client_id, 0) as client_key'),
                DB::raw('MAX(client_name) as client_name'),
                DB::raw('SUM(balance) as total_due'),
                DB::raw('COUNT(*) as nb_invoices'),
                DB::raw('MIN(due_date) as oldest_due'),
                DB::raw("SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() THEN 1 ELSE 0 END) as nb_overdue")
            )
            ->groupBy('client_key')
            ->orderByDesc('total_due')
            ->limit(10)
            ->get()
            ->map(function ($row) use ($today) {
                if ($row->oldest_due) {
                    $oldest = Carbon::parse($row->oldest_due)->startOfDay();
                    $row->oldest_days = (int) round($oldest->diffInDays($today, false));
                } else {
                    $row->oldest_days = null;
                }
                $row->is_overdue = $row->nb_overdue > 0;
                return $row;
            });

        // ===== Répartition modes de paiement (mois en cours) =====
        $paymentMethods = Payment::where('company_id', $companyId)
            ->where('is_confirmed', true)
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // ===== DSO (Days Sales Outstanding) sur 90 derniers jours =====
        // DSO = (Créances clients / CA TTC sur la période) × nb jours
        $dsoPeriodStart = $now->copy()->subDays(90);
        $caDso = (clone $invoiceBase)->whereBetween('invoice_date', [$dsoPeriodStart, $now])->sum('total_amount');
        $receivablesDso = (clone $invoiceBase)->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance');
        $dso = $caDso > 0 ? round(($receivablesDso / $caDso) * 90) : null;

        return view('reports.index', compact(
            'caMonth', 'caPrevMonth', 'variationMonth',
            'caYear', 'caPrevYearSamePeriod',
            'invoicesIssuedMonth', 'invoicesPaidMonth', 'invoicesOverdue', 'totalOverdueAmount',
            'avgTicket',
            'monthLabels', 'billedSeries', 'collectedSeries',
            'aging',
            'topOverdueClients',
            'paymentMethods',
            'dso'
        ));
    }
}
