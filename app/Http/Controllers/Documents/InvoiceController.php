<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
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
        $user      = $request->user();
        $companyId = $user->company_id;

        $query = Invoice::where('company_id', $companyId)
            ->invoicesOnly()
            ->with(['client', 'user', 'deliveryNotes']);

        // Filtrage par site pour les utilisateurs non-admins
        if (!$user->hasAccessToAllSites()) {
            $query->whereIn('site_id', $user->getSiteIds());
        }

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
        $baseStats = Invoice::where('company_id', $companyId)->invoicesOnly();
        if (!$user->hasAccessToAllSites()) {
            $baseStats->whereIn('site_id', $user->getSiteIds());
        }
        $stats = [
            'total'   => (clone $baseStats)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'paid'    => (clone $baseStats)->where('status', '!=', 'cancelled')->sum('paid_amount'),
            'pending' => (clone $baseStats)->where('status', '!=', 'cancelled')->pending()->sum('balance'),
            'overdue' => (clone $baseStats)->overdue()->sum('balance'),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $clients = $user->hasFeature('save_clients')
            ? Client::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->where('is_active', true)
            ->ordered()
            ->get();

        $sitesQuery = Site::where('company_id', $companyId)->where('is_active', true);
        if (!$user->hasAccessToAllSites()) {
            $sitesQuery->whereIn('id', $user->getSiteIds());
        }
        $sites = $sitesQuery->ordered()->get();

        $selectedClient = null;
        if ($user->hasFeature('save_clients') && $clientId = $request->input('client_id')) {
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

            // Promo code lookup
            $promoModel    = null;
            $discountType  = $request->discount_type ?: null;
            $discountValue = $discountType ? ($request->discount_value ?? 0) : 0;

            if ($request->filled('promo_code')) {
                $promoModel = Promotion::where('company_id', $user->company_id)
                    ->where('code', strtoupper($request->promo_code))
                    ->first();
            }

            $invoiceData = [
                'company_id'     => $user->company_id,
                'site_id'        => $request->site_id ?? $user->getPrimarySite()?->id,
                'user_id'        => $user->id,
                'client_id'      => $request->client_id,
                'invoice_date'   => $request->invoice_date ?? now(),
                'due_date'       => $request->due_date ?? now()->addDays(30),
                'status'         => 'sent',
                'payment_status' => 'unpaid',
                'discount_type'  => $discountType,
                'discount_value' => $discountValue,
                'promo_id'       => $promoModel?->id,
                'promo_code'     => $promoModel ? $promoModel->code : null,
                'notes'          => $request->notes,
                'terms'          => $request->terms,
            ];

            if ($client = Client::find($request->client_id)) {
                $invoiceData = array_merge($invoiceData, [
                    'client_name'    => $client->display_name,
                    'client_email'   => $client->email,
                    'client_phone'   => $client->phone,
                    'client_address' => $client->full_address,
                ]);
            } else {
                $invoiceData['client_name']    = $request->client_name;
                $invoiceData['client_email']   = $request->client_email;
                $invoiceData['client_phone']   = $request->client_phone;
                $invoiceData['client_address'] = $request->client_address;
            }

            $invoice = Invoice::create($invoiceData);

            // Ajouter les lignes
            foreach ($request->items as $index => $item) {
                $invoice->items()->create([
                    'product_id'     => $item['product_id'] ?? null,
                    'description'    => $item['description'],
                    'details'        => $item['details'] ?? null,
                    'type'           => $item['type'] ?? 'product',
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'discount_type'  => $item['discount_type'] ?? null,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'sort_order'     => $index,
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
                            'tax_id'       => $tax->id,
                            'tax_name'     => $tax->name,
                            'tax_rate'     => $tax->rate,
                            'apply_to'     => $tax->apply_to,
                            'taxable_base' => $taxableBase,
                            'tax_amount'   => round($taxableBase * ($tax->rate / 100), 2),
                        ]);
                    }
                }
            }

            $invoice->calculateTotals();

            // Incrémenter le compteur de la promo si utilisée
            $promoModel?->increment('uses_count');

            // Paiement immédiat
            if ($request->boolean('payment_immediate')) {
                Payment::create([
                    'company_id'     => $user->company_id,
                    'invoice_id'     => $invoice->id,
                    'user_id'        => $user->id,
                    'site_id'        => $invoice->site_id,
                    'amount'         => $invoice->total_amount,
                    'payment_date'   => now(),
                    'payment_method' => $request->input('payment_method', 'cash'),
                    'reference'      => $request->input('payment_reference') ?: null,
                    'is_confirmed'   => true,
                    'confirmed_at'   => now(),
                    'confirmed_by'   => $user->id,
                ]);
                // Le boot hook de Payment appelle automatiquement $invoice->recordPayment()
            }

            // Livraison immédiate
            if ($request->boolean('delivery_immediate')) {
                $invoice->loadMissing('items');
                $dn = DeliveryNote::create([
                    'company_id'       => $invoice->company_id,
                    'site_id'          => $invoice->site_id,
                    'user_id'          => $user->id,
                    'invoice_id'       => $invoice->id,
                    'client_id'        => $invoice->client_id,
                    'client_name'      => $invoice->client_name,
                    'client_email'     => $invoice->client_email,
                    'client_phone'     => $invoice->client_phone,
                    'client_address'   => $invoice->client_address,
                    'delivery_address' => $invoice->client_address,
                    'planned_date'     => now()->toDateString(),
                    'delivered_date'   => now()->toDateString(),
                    'status'           => 'delivered',
                    'notes'            => 'Livraison immédiate',
                ]);

                foreach ($invoice->items as $idx => $item) {
                    $dn->items()->create([
                        'product_id'  => $item->product_id,
                        'description' => $item->description,
                        'quantity'    => $item->quantity,
                        'sort_order'  => $idx,
                    ]);
                    // Décrémenter le stock pour les produits trackés
                    if ($item->product_id && $item->type === 'product') {
                        Product::find($item->product_id)?->decrementStock($item->quantity);
                    }
                }
            }

            DB::commit();

            $msg = 'Facture créée avec succès.';
            if ($request->boolean('payment_immediate')) {
                $msg .= ' Paiement enregistré.';
            }
            if ($request->boolean('delivery_immediate')) {
                $msg .= ' Bon de livraison créé.';
            }

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', $msg);

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

        $user      = $request->user();
        $companyId = $user->company_id;

        $clients = $user->hasFeature('save_clients')
            ? Client::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $products = Product::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('company_id', $companyId)->where('is_active', true)->ordered()->get();
        $sitesQuery = Site::where('company_id', $companyId)->where('is_active', true);
        if (!$user->hasAccessToAllSites()) {
            $sitesQuery->whereIn('id', $user->getSiteIds());
        }
        $sites = $sitesQuery->ordered()->get();

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

            // Promo code handling for update
            $discountType  = $request->discount_type ?: null;
            $discountValue = $discountType ? ($request->discount_value ?? 0) : 0;
            $promoModel    = null;
            $newPromoCode  = $request->filled('promo_code') ? strtoupper($request->promo_code) : null;

            if ($newPromoCode) {
                $promoModel = Promotion::where('company_id', $invoice->company_id)
                    ->where('code', $newPromoCode)
                    ->first();
            }

            $invoice->update([
                'client_id'      => $request->client_id,
                'site_id'        => $request->site_id,
                'invoice_date'   => $request->invoice_date,
                'due_date'       => $request->due_date,
                'discount_type'  => $discountType,
                'discount_value' => $discountValue,
                'promo_id'       => $promoModel?->id,
                'promo_code'     => $promoModel ? $promoModel->code : null,
                'notes'          => $request->notes,
                'terms'          => $request->terms,
            ]);

            if ($client = Client::find($request->client_id)) {
                $invoice->update([
                    'client_name'    => $client->display_name,
                    'client_email'   => $client->email,
                    'client_phone'   => $client->phone,
                    'client_address' => $client->full_address,
                ]);
            } else {
                $invoice->update([
                    'client_name'    => $request->client_name,
                    'client_email'   => $request->client_email,
                    'client_phone'   => $request->client_phone,
                    'client_address' => $request->client_address,
                ]);
            }

            // Recréer les lignes
            $invoice->items()->delete();
            foreach ($request->items as $index => $item) {
                $invoice->items()->create([
                    'product_id'     => $item['product_id'] ?? null,
                    'description'    => $item['description'],
                    'details'        => $item['details'] ?? null,
                    'type'           => $item['type'] ?? 'product',
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'discount_type'  => $item['discount_type'] ?? null,
                    'discount_value' => $item['discount_value'] ?? 0,
                    'sort_order'     => $index,
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
                            'tax_id'       => $tax->id,
                            'tax_name'     => $tax->name,
                            'tax_rate'     => $tax->rate,
                            'apply_to'     => $tax->apply_to,
                            'taxable_base' => $taxableBase,
                            'tax_amount'   => round($taxableBase * ($tax->rate / 100), 2),
                        ]);
                    }
                }
            }

            $invoice->calculateTotals();

            // Incrémenter le compteur promo si c'est un nouveau code
            if ($promoModel && $newPromoCode !== $invoice->getOriginal('promo_code')) {
                $promoModel->increment('uses_count');
            }

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

        $invoice->load(['company', 'client', 'items', 'taxes', 'payments', 'deliveryNotes']);

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
     * Générer un code PIN de livraison pour la facture
     */
    public function generatePin(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($request, $invoice);

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Impossible de générer un code pour une facture annulée.');
        }

        $pin = Invoice::generateDeliveryPin();

        $invoice->update([
            'delivery_pin'              => $pin,
            'delivery_pin_generated_at' => now(),
        ]);

        return back()->with('pin_generated', $pin);
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
