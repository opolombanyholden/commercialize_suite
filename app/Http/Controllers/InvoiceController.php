<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Tax;
use App\Services\NumberToWordsService;
use App\Services\PdfService;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected TaxCalculatorService $taxCalculator;
    protected PdfService $pdfService;

    public function __construct(TaxCalculatorService $taxCalculator, PdfService $pdfService)
    {
        $this->taxCalculator = $taxCalculator;
        $this->pdfService = $pdfService;

        $this->middleware('permission:invoices.view')->only(['index', 'show']);
        $this->middleware('permission:invoices.create')->only(['create', 'store']);
        $this->middleware('permission:invoices.edit')->only(['edit', 'update']);
        $this->middleware('permission:invoices.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Invoice::where('company_id', auth()->user()->company_id)
            ->with(['client', 'user']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        // Filtre par statut de paiement
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('payment_status', $request->payment_status);
            }
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $invoices = $query->paginate(20)->withQueryString();

        // Statistiques
        $companyId = auth()->user()->company_id;
        $stats = [
            'total_unpaid' => Invoice::where('company_id', $companyId)->unpaid()->sum('balance'),
            'total_overdue' => Invoice::where('company_id', $companyId)->overdue()->sum('balance'),
            'count_overdue' => Invoice::where('company_id', $companyId)->overdue()->count(),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $clients = Client::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::find($request->client_id);
        }

        return view('invoices.create', compact('clients', 'products', 'taxes', 'selectedClient'));
    }

    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();
        $companyId = auth()->user()->company_id;

        // Créer la facture
        $invoice = Invoice::create([
            'company_id' => $companyId,
            'site_id' => $data['site_id'] ?? auth()->user()->getPrimarySite()?->id,
            'user_id' => auth()->id(),
            'client_id' => $data['client_id'] ?? null,
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'client_address' => $data['client_address'] ?? null,
            'client_city' => $data['client_city'] ?? null,
            'client_postal_code' => $data['client_postal_code'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        // Ajouter les items
        $sortOrder = 0;
        foreach ($data['items'] as $itemData) {
            $invoice->items()->create([
                'product_id' => $itemData['product_id'] ?? null,
                'description' => $itemData['description'],
                'details' => $itemData['details'] ?? null,
                'type' => $itemData['type'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total' => round($itemData['quantity'] * $itemData['unit_price'], 2),
                'sort_order' => $sortOrder++,
            ]);
        }

        // Ajouter les taxes
        if (!empty($data['taxes'])) {
            foreach ($data['taxes'] as $taxData) {
                $tax = Tax::find($taxData['tax_id']);
                if ($tax) {
                    $items = $invoice->items->map(fn($item) => [
                        'type' => $item->type,
                        'total' => $item->total,
                    ])->toArray();

                    $calculated = $this->taxCalculator->calculate($items, [[
                        'rate' => $tax->rate,
                        'apply_to' => $taxData['apply_to'],
                    ]]);

                    $invoice->taxes()->create([
                        'tax_id' => $tax->id,
                        'tax_name' => $tax->name,
                        'tax_rate' => $tax->rate,
                        'apply_to' => $taxData['apply_to'],
                        'taxable_base' => $calculated['taxes'][0]['base'] ?? 0,
                        'tax_amount' => $calculated['taxes'][0]['amount'] ?? 0,
                    ]);
                }
            }
        }

        // Calculer les totaux
        $invoice->calculateTotals();

        // Convertir en lettres
        $invoice->update([
            'total_in_words' => NumberToWordsService::toWords($invoice->total_amount),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture créée avec succès.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'user', 'site', 'items.product', 'taxes', 'payments', 'quote']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (!$invoice->canBeEdited()) {
            return back()->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $companyId = auth()->user()->company_id;

        $clients = Client::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $invoice->load(['items', 'taxes']);

        return view('invoices.edit', compact('invoice', 'clients', 'products', 'taxes'));
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (!$invoice->canBeEdited()) {
            return back()->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $data = $request->validated();

        $invoice->update([
            'client_id' => $data['client_id'] ?? null,
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'client_address' => $data['client_address'] ?? null,
            'client_city' => $data['client_city'] ?? null,
            'client_postal_code' => $data['client_postal_code'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
        ]);

        // Supprimer et recréer items/taxes
        $invoice->items()->delete();
        $invoice->taxes()->delete();

        $sortOrder = 0;
        foreach ($data['items'] as $itemData) {
            $invoice->items()->create([
                'product_id' => $itemData['product_id'] ?? null,
                'description' => $itemData['description'],
                'details' => $itemData['details'] ?? null,
                'type' => $itemData['type'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total' => round($itemData['quantity'] * $itemData['unit_price'], 2),
                'sort_order' => $sortOrder++,
            ]);
        }

        if (!empty($data['taxes'])) {
            $invoice->refresh();
            foreach ($data['taxes'] as $taxData) {
                $tax = Tax::find($taxData['tax_id']);
                if ($tax) {
                    $items = $invoice->items->map(fn($item) => [
                        'type' => $item->type,
                        'total' => $item->total,
                    ])->toArray();

                    $calculated = $this->taxCalculator->calculate($items, [[
                        'rate' => $tax->rate,
                        'apply_to' => $taxData['apply_to'],
                    ]]);

                    $invoice->taxes()->create([
                        'tax_id' => $tax->id,
                        'tax_name' => $tax->name,
                        'tax_rate' => $tax->rate,
                        'apply_to' => $taxData['apply_to'],
                        'taxable_base' => $calculated['taxes'][0]['base'] ?? 0,
                        'tax_amount' => $calculated['taxes'][0]['amount'] ?? 0,
                    ]);
                }
            }
        }

        $invoice->calculateTotals();
        $invoice->update([
            'total_in_words' => NumberToWordsService::toWords($invoice->total_amount),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture mise à jour avec succès.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        if (!$invoice->canBeDeleted()) {
            return back()->with('error', 'Cette facture ne peut pas être supprimée.');
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Générer le PDF
     */
    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['company', 'items', 'taxes']);

        return $this->pdfService->download(
            'pdf.invoice',
            ['invoice' => $invoice, 'company' => $invoice->company],
            'facture-' . $invoice->invoice_number . '.pdf'
        );
    }

    /**
     * Marquer comme envoyée
     */
    public function markAsSent(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->markAsSent();

        return back()->with('success', 'Facture marquée comme envoyée.');
    }

    /**
     * Dupliquer une facture
     */
    public function duplicate(Invoice $invoice)
    {
        $this->authorize('create', Invoice::class);

        $newInvoice = Invoice::create([
            'company_id' => $invoice->company_id,
            'site_id' => $invoice->site_id,
            'user_id' => auth()->id(),
            'client_id' => $invoice->client_id,
            'client_name' => $invoice->client_name,
            'client_email' => $invoice->client_email,
            'client_phone' => $invoice->client_phone,
            'client_address' => $invoice->client_address,
            'client_city' => $invoice->client_city,
            'client_postal_code' => $invoice->client_postal_code,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $invoice->subtotal,
            'tax_amount' => $invoice->tax_amount,
            'total_amount' => $invoice->total_amount,
            'total_in_words' => $invoice->total_in_words,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        // Copier les items
        foreach ($invoice->items as $item) {
            $newInvoice->items()->create($item->only([
                'product_id', 'description', 'details', 'type',
                'quantity', 'unit_price', 'total', 'sort_order'
            ]));
        }

        // Copier les taxes
        foreach ($invoice->taxes as $tax) {
            $newInvoice->taxes()->create($tax->only([
                'tax_id', 'tax_name', 'tax_rate', 'apply_to',
                'taxable_base', 'tax_amount'
            ]));
        }

        return redirect()
            ->route('invoices.edit', $newInvoice)
            ->with('success', 'Facture dupliquée. Vous pouvez maintenant la modifier.');
    }
}
