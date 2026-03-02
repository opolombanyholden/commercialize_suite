<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;

        $this->middleware('permission:payments.view')->only(['index', 'show']);
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.edit')->only(['edit', 'update']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Payment::where('company_id', auth()->user()->company_id)
            ->with(['invoice', 'user']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($q) use ($search) {
                      $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par méthode
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $payments = $query->paginate(20)->withQueryString();

        // Statistiques du mois
        $companyId = auth()->user()->company_id;
        $stats = [
            'month_total' => Payment::where('company_id', $companyId)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'month_count' => Payment::where('company_id', $companyId)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->count(),
        ];

        $paymentMethods = PaymentMethod::cases();

        return view('payments.index', compact('payments', 'stats', 'paymentMethods'));
    }

    /**
     * Formulaire de paiement pour une facture
     */
    public function create(Invoice $invoice)
    {
        $this->authorize('create', Payment::class);

        if ($invoice->isPaid()) {
            return back()->with('error', 'Cette facture est déjà entièrement payée.');
        }

        $paymentMethods = PaymentMethod::cases();

        return view('payments.create', compact('invoice', 'paymentMethods'));
    }

    public function store(PaymentRequest $request, Invoice $invoice)
    {
        $this->authorize('create', Payment::class);

        if ($invoice->isPaid()) {
            return back()->with('error', 'Cette facture est déjà entièrement payée.');
        }

        $data = $request->validated();

        $payment = Payment::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice', 'user', 'confirmedBy']);

        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        $invoice = $payment->invoice;
        $payment->delete();

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Paiement supprimé avec succès.');
    }

    /**
     * Générer le reçu PDF
     */
    public function receipt(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice.company', 'user']);

        return $this->pdfService->download(
            'pdf.payment-receipt',
            [
                'payment' => $payment,
                'invoice' => $payment->invoice,
                'company' => $payment->invoice->company,
            ],
            'recu-' . $payment->payment_number . '.pdf'
        );
    }

    /**
     * Paiements d'une facture
     */
    public function forInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $payments = $invoice->payments()
            ->with('user')
            ->orderBy('payment_date', 'desc')
            ->get();

        return view('payments.for-invoice', compact('invoice', 'payments'));
    }
}
