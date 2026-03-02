<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Site;
use App\Models\Tax;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:invoices.view')->only(['index', 'show']);
        $this->middleware('permission:invoices.create')->only(['create', 'store']);
        $this->middleware('permission:invoices.edit')->only(['edit', 'update']);
        $this->middleware('permission:invoices.delete')->only('destroy');
        $this->middleware('permission:invoices.send')->only('send');
    }

    /**
     * Liste des factures
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Invoice::where('company_id', $companyId)
            ->invoicesOnly()
            ->with(['client', 'user', 'deliveryNotes']);

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filtre par statut de paiement
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // Filtre factures en retard
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Filtre par période
        if ($from = $request->input('date_from')) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('invoice_date', '<=', $to);
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        // Statistiques (factures uniquement, pas les avoirs)
        $stats = [
            'total' => Invoice::where('company_id', $companyId)->invoicesOnly()
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
            'paid' => Invoice::where('company_id', $companyId)->invoicesOnly()
                ->where('status', '!=', 'cancelled')
                ->sum('paid_amount'),
            'pending' => Invoice::where('company_id', $companyId)->invoicesOnly()
                ->where('status', '!=', 'cancelled')
                ->pending()
                ->sum('balance'),
            'overdue' => Invoice::where('company_id', $companyId)->invoicesOnly()
                ->overdue()
                ->sum('balance'),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

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
            ->ordered()
            ->get();

        $sites = Site::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $selectedClient = null;
        if ($clientId = $request->input('client_id')) {
            $selectedClient = Client::find($clientId);
        }

        return view('invoices.create', compact(
            'clients', 'products', 'taxes', 'sites', 'selectedClient'
        ));
    }

    /**
     * Enregistrer une nouvelle facture
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            $invoiceData = [
                'company_id' => $user->company_id,
                'site_id' => $request->site_id ?? $user->getPrimarySite()?->id,
                'user_id' => $user->id,
                'client_id' => $request->client_id,
                'invoice_date' => $request->invoice_date ?? now(),
                'due_date' => $request->due_date ?? now()->addDays(30),
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'notes' => $request->notes,
                'terms' => $request->terms,
            ];

            if ($client = Client::find($request->client_id)) {
                $invoiceData = array_merge($invoiceData, [
                    'client_name' => $client->display_name,
                    'client_email' => $client->email,
                    'client_phone' => $client->phone,
                    'client_address' => $client->full_address,
                ]);
            }

            $invoice = Invoice::create($invoiceData);

            // Ajouter les lignes
            foreach ($request->items as $index => $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'details' => $item['details'] ?? null,
                    'type' => $item['type'] ?? 'product',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            // Ajouter les taxes
            if ($request->filled('taxes')) {
                foreach ($request->taxes as $taxId) {
                    $tax = Tax::find($taxId);
                    if ($tax) {
                        $taxableBase = $invoice->items()
                            ->when($tax->apply_to === 'products', fn($q) => $q->where('type', 'product'))
                            ->when($tax->apply_to === 'services', fn($q) => $q->where('type', 'service'))
                            ->sum('total');

                        $invoice->taxes()->create([
                            'tax_id' => $tax->id,
                            'tax_name' => $tax->name,
                            'tax_rate' => $tax->rate,
                            'apply_to' => $tax->apply_to,
                            'taxable_base' => $taxableBase,
                            'tax_amount' => round($taxableBase * ($tax->rate / 100), 2),
                        ]);
                    }
                }
            }

            $invoice->calculateTotals();

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Facture créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une facture
     */
    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeCompany($request, $invoice);

        $invoice->load(['company', 'client', 'user', 'site', 'items.product', 'taxes', 'payments', 'quote', 'deliveryNotes.items', 'deliveryReturns']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Invoice $invoice): View|RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        // Seules les factures en brouillon peuvent être modifiées
        if ($invoice->status !== 'draft') {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Seules les factures en brouillon peuvent être modifiées.');
        }

        $companyId = $request->user()->company_id;

        $clients = Client::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('company_id', $companyId)->where('is_active', true)->ordered()->get();
        $sites = Site::where('company_id', $companyId)->where('is_active', true)->get();

        $invoice->load(['items', 'taxes']);
        $selectedTaxes = $invoice->taxes->pluck('tax_id')->toArray();

        return view('invoices.edit', compact(
            'invoice', 'clients', 'products', 'taxes', 'sites', 'selectedTaxes'
        ));
    }

    /**
     * Mettre à jour une facture
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Seules les factures en brouillon peuvent être modifiées.');
        }

        try {
            DB::beginTransaction();

            $invoice->update([
                'client_id' => $request->client_id,
                'site_id' => $request->site_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            if ($client = Client::find($request->client_id)) {
                $invoice->update([
                    'client_name' => $client->display_name,
                    'client_email' => $client->email,
                    'client_phone' => $client->phone,
                    'client_address' => $client->full_address,
                ]);
            }

            // Recréer les lignes
            $invoice->items()->delete();
            foreach ($request->items as $index => $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'details' => $item['details'] ?? null,
                    'type' => $item['type'] ?? 'product',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            // Recréer les taxes
            $invoice->taxes()->delete();
            if ($request->filled('taxes')) {
                foreach ($request->taxes as $taxId) {
                    $tax = Tax::find($taxId);
                    if ($tax) {
                        $taxableBase = $invoice->items()
                            ->when($tax->apply_to === 'products', fn($q) => $q->where('type', 'product'))
                            ->when($tax->apply_to === 'services', fn($q) => $q->where('type', 'service'))
                            ->sum('total');

                        $invoice->taxes()->create([
                            'tax_id' => $tax->id,
                            'tax_name' => $tax->name,
                            'tax_rate' => $tax->rate,
                            'apply_to' => $tax->apply_to,
                            'taxable_base' => $taxableBase,
                            'tax_amount' => round($taxableBase * ($tax->rate / 100), 2),
                        ]);
                    }
                }
            }

            $invoice->calculateTotals();

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Facture mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Seules les factures en brouillon peuvent être supprimées.');
        }

        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Impossible de supprimer une facture avec des paiements.');
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Télécharger le PDF
     */
    public function pdf(Request $request, Invoice $invoice, PdfService $pdfService)
    {
        $this->authorizeCompany($request, $invoice);

        $invoice->load(['company', 'client', 'items', 'taxes', 'payments']);

        return $pdfService->download(
            'pdf.invoice',
            ['invoice' => $invoice, 'company' => $invoice->company],
            'facture-' . $invoice->invoice_number . '.pdf'
        );
    }

    /**
     * Marquer comme envoyée
     */
    public function send(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        $invoice->markAsSent();

        return back()->with('success', 'Facture marquée comme envoyée.');
    }

    /**
     * Marquer comme annulée
     */
    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Impossible d\'annuler une facture avec des paiements.');
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Facture annulée.');
    }

    /**
     * Dupliquer une facture
     */
    public function duplicate(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        try {
            DB::beginTransaction();

            $newInvoice = $invoice->replicate([
                'invoice_number', 'status', 'payment_status', 'sent_at', 'viewed_at',
                'paid_at', 'paid_amount', 'balance', 'quote_id', 'pdf_path', 'pdf_generated_at'
            ]);
            
            $newInvoice->invoice_date = now();
            $newInvoice->due_date = now()->addDays(30);
            $newInvoice->status = 'draft';
            $newInvoice->payment_status = 'unpaid';
            $newInvoice->paid_amount = 0;
            $newInvoice->save();

            foreach ($invoice->items as $item) {
                $newItem = $item->replicate(['invoice_id']);
                $newItem->invoice_id = $newInvoice->id;
                $newItem->save();
            }

            foreach ($invoice->taxes as $tax) {
                $newTax = $tax->replicate(['invoice_id']);
                $newTax->invoice_id = $newInvoice->id;
                $newTax->save();
            }

            $newInvoice->calculateTotals();

            DB::commit();

            return redirect()
                ->route('invoices.edit', $newInvoice)
                ->with('success', 'Facture dupliquée.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la duplication.');
        }
    }

    /**
     * Mettre à jour le statut d'une facture
     */
    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        $request->validate(['status' => ['required', 'in:draft,sent,paid,partial,cancelled']]);

        $newStatus = $request->status;
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'sent' && !$invoice->sent_at) {
            $updateData['sent_at'] = now();
        }

        if ($newStatus === 'paid') {
            $updateData['payment_status'] = 'paid';
            $updateData['paid_amount']    = $invoice->total_amount;
            $updateData['balance']        = 0;
            $updateData['paid_at']        = now();
        }

        if ($newStatus === 'partial') {
            $updateData['payment_status'] = 'partial';
            // Rétablir le statut fonctionnel si nécessaire
            if (in_array($invoice->status, ['draft', 'paid', 'cancelled'])) {
                $updateData['status'] = 'sent';
            } else {
                unset($updateData['status']);
            }
        }

        $invoice->update($updateData);

        $labels = [
            'draft'     => 'Brouillon',
            'sent'      => 'Envoyée',
            'paid'      => 'Payée',
            'partial'   => 'Paiement partiel',
            'cancelled' => 'Annulée',
        ];

        return back()->with('success', 'Statut mis à jour : ' . ($labels[$newStatus] ?? $newStatus) . '.');
    }

    /**
     * Vérifier que la facture appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Invoice $invoice): void
    {
        if ($invoice->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à cette facture.');
        }
    }
}
