<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payments.view')->only(['index', 'show']);
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.edit')->only(['edit', 'update']);
        $this->middleware('permission:payments.delete')->only('destroy');
    }

    /**
     * Liste des paiements
     */
    public function index(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $query = Payment::where('company_id', $companyId)
            ->with(['invoice', 'user']);

        // Filtrage par site pour les utilisateurs non-admins
        if (!$user->hasAccessToAllSites()) {
            $siteIds = $user->getSiteIds();
            $query->whereIn('site_id', $siteIds);
        }

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($q) use ($search) {
                      $q->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par méthode
        if ($method = $request->input('method')) {
            $query->where('payment_method', $method);
        }

        // Filtre par période
        if ($from = $request->input('from')) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('payment_date', '<=', $to);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        // Statistiques (scope identique au filtrage ci-dessus)
        $statsQuery = Payment::where('company_id', $companyId);
        if (!$user->hasAccessToAllSites()) {
            $statsQuery->whereIn('site_id', $user->getSiteIds());
        }
        $stats = [
            'total_amount' => (clone $statsQuery)->sum('amount'),
            'this_month'   => (clone $statsQuery)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'count'        => (clone $statsQuery)->count(),
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    /**
     * Formulaire de création (depuis une facture)
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        // Factures avec solde restant
        $invoices = Invoice::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', '!=', 'cancelled')
            ->with('client')
            ->orderBy('due_date')
            ->get();

        $selectedInvoice = null;
        if ($invoiceId = $request->input('invoice_id')) {
            $selectedInvoice = Invoice::find($invoiceId);
        }

        return view('payments.create', compact('invoices', 'selectedInvoice'));
    }

    /**
     * Enregistrer un paiement
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $invoice = Invoice::findOrFail($request->invoice_id);

        // Vérifier l'accès
        if ($invoice->company_id !== $user->company_id) {
            abort(403);
        }

        // Vérifier le montant
        if ($request->amount > $invoice->balance) {
            return back()
                ->withInput()
                ->with('error', 'Le montant ne peut pas dépasser le solde restant (' . format_currency($invoice->balance) . ')');
        }

        $payment = Payment::create([
            'company_id' => $user->company_id,
            'invoice_id' => $invoice->id,
            'user_id'    => $user->id,
            'site_id'    => $invoice->site_id ?? $user->getPrimarySite()?->id,
            'amount'     => $request->amount,
            'payment_date' => $request->payment_date ?? now(),
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $user->id,
        ]);

        // Le modèle Payment met à jour automatiquement la facture

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    /**
     * Afficher un paiement
     */
    public function show(Request $request, Payment $payment): View
    {
        $this->authorizeCompany($request, $payment);

        $payment->load(['invoice.client', 'user', 'confirmedBy']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Supprimer un paiement
     */
    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizeCompany($request, $payment);

        $invoice = $payment->invoice;
        $payment->delete();

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Paiement supprimé.');
    }

    /**
     * Télécharger le reçu PDF
     */
    public function receipt(Request $request, Payment $payment, PdfService $pdfService)
    {
        $this->authorizeCompany($request, $payment);

        $payment->load(['invoice.client', 'company', 'user']);

        return $pdfService->download(
            'pdf.payment-receipt',
            ['payment' => $payment, 'company' => $payment->company],
            'recu-' . $payment->payment_number . '.pdf'
        );
    }

    /**
     * Vérifier que le paiement appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Payment $payment): void
    {
        if ($payment->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à ce paiement.');
        }
    }
}
