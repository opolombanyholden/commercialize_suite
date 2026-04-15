<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteTax;
use App\Models\Tax;
use App\Services\NumberToWordsService;
use App\Services\PdfService;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    protected TaxCalculatorService $taxCalculator;
    protected PdfService $pdfService;

    public function __construct(TaxCalculatorService $taxCalculator, PdfService $pdfService)
    {
        $this->taxCalculator = $taxCalculator;
        $this->pdfService = $pdfService;

        $this->middleware('permission:quotes.view')->only(['index', 'show']);
        $this->middleware('permission:quotes.create')->only(['create', 'store']);
        $this->middleware('permission:quotes.edit')->only(['edit', 'update']);
        $this->middleware('permission:quotes.delete')->only(['destroy']);
        $this->middleware('permission:quotes.convert')->only(['convert']);
    }

    public function index(Request $request)
    {
        $query = Quote::where('company_id', auth()->user()->company_id)
            ->with(['client', 'user']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->where('quote_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('quote_date', '<=', $request->date_to);
        }

        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $quotes = $query->paginate(20)->withQueryString();

        return view('quotes.index', compact('quotes'));
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

        // Pré-remplir avec un client si fourni
        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::find($request->client_id);
        }

        return view('quotes.create', compact('clients', 'products', 'taxes', 'selectedClient'));
    }

    public function store(QuoteRequest $request)
    {
        $data = $request->validated();
        $companyId = auth()->user()->company_id;

        // Créer le devis
        $quote = Quote::create([
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
            'quote_date' => $data['quote_date'],
            'valid_until' => $data['valid_until'] ?? null,
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            'subject' => $data['subject'] ?? null,
            'status' => 'draft',
        ]);

        // Ajouter les items
        $sortOrder = 0;
        foreach ($data['items'] as $itemData) {
            $quote->items()->create([
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
                    // Calculer la base imposable
                    $items = $quote->items->map(fn($item) => [
                        'type' => $item->type,
                        'total' => $item->total,
                    ])->toArray();

                    $calculated = $this->taxCalculator->calculate($items, [[
                        'rate' => $tax->rate,
                        'apply_to' => $taxData['apply_to'],
                    ]]);

                    $quote->taxes()->create([
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
        $quote->calculateTotals();

        // Convertir en lettres
        $quote->update([
            'total_in_words' => NumberToWordsService::toWords($quote->total_amount),
        ]);

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Devis créé avec succès.');
    }

    public function show(Quote $quote)
    {
        $this->authorize('view', $quote);

        $quote->load(['client', 'user', 'site', 'items.product', 'taxes', 'convertedInvoice']);

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $this->authorize('update', $quote);

        if (!$quote->canBeEdited()) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
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

        $quote->load(['items', 'taxes']);

        return view('quotes.edit', compact('quote', 'clients', 'products', 'taxes'));
    }

    public function update(QuoteRequest $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        if (!$quote->canBeEdited()) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $data = $request->validated();

        // Mettre à jour le devis
        $quote->update([
            'client_id' => $data['client_id'] ?? null,
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'] ?? null,
            'client_phone' => $data['client_phone'] ?? null,
            'client_address' => $data['client_address'] ?? null,
            'client_city' => $data['client_city'] ?? null,
            'client_postal_code' => $data['client_postal_code'] ?? null,
            'quote_date' => $data['quote_date'],
            'valid_until' => $data['valid_until'] ?? null,
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            'subject' => $data['subject'] ?? null,
        ]);

        // Supprimer les anciens items et taxes
        $quote->items()->delete();
        $quote->taxes()->delete();

        // Recréer les items
        $sortOrder = 0;
        foreach ($data['items'] as $itemData) {
            $quote->items()->create([
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

        // Recréer les taxes
        if (!empty($data['taxes'])) {
            $quote->refresh();
            foreach ($data['taxes'] as $taxData) {
                $tax = Tax::find($taxData['tax_id']);
                if ($tax) {
                    $items = $quote->items->map(fn($item) => [
                        'type' => $item->type,
                        'total' => $item->total,
                    ])->toArray();

                    $calculated = $this->taxCalculator->calculate($items, [[
                        'rate' => $tax->rate,
                        'apply_to' => $taxData['apply_to'],
                    ]]);

                    $quote->taxes()->create([
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

        // Recalculer
        $quote->calculateTotals();
        $quote->update([
            'total_in_words' => NumberToWordsService::toWords($quote->total_amount),
        ]);

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Devis mis à jour avec succès.');
    }

    public function destroy(Quote $quote)
    {
        $this->authorize('delete', $quote);

        if (!$quote->canBeDeleted()) {
            return back()->with('error', 'Ce devis ne peut pas être supprimé.');
        }

        $quote->delete();

        return redirect()
            ->route('quotes.index')
            ->with('success', 'Devis supprimé avec succès.');
    }

    /**
     * Générer le PDF
     */
    public function pdf(Request $request, Quote $quote)
    {
        $this->authorize('view', $quote);

        $quote->load(['company', 'items', 'taxes']);

        return $this->pdfService->download(
            'pdf.quote',
            [
                'quote' => $quote,
                'company' => $quote->company,
                'style' => \App\Models\DocumentStyle::forDocument($quote->company_id, 'quote'),
                'withSignature' => $request->boolean('signature'),
            ],
            'devis-' . $quote->quote_number . '.pdf'
        );
    }

    /**
     * Convertir en facture
     */
    public function convert(Quote $quote)
    {
        $this->authorize('convert', $quote);

        if (!$quote->canBeConverted()) {
            return back()->with('error', 'Ce devis ne peut pas être converti en facture.');
        }

        $invoice = Invoice::createFromQuote($quote);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Devis converti en facture avec succès.');
    }

    /**
     * Marquer comme envoyé
     */
    public function markAsSent(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->markAsSent();

        return back()->with('success', 'Devis marqué comme envoyé.');
    }

    /**
     * Marquer comme accepté
     */
    public function markAsAccepted(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->markAsAccepted();

        return back()->with('success', 'Devis marqué comme accepté.');
    }

    /**
     * Marquer comme refusé
     */
    public function markAsDeclined(Quote $quote)
    {
        $this->authorize('update', $quote);

        $quote->markAsDeclined();

        return back()->with('success', 'Devis marqué comme refusé.');
    }
}
