<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\StoreQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Quote;
use App\Models\Site;
use App\Models\Tax;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:quotes.view')->only(['index', 'show']);
        $this->middleware('permission:quotes.create')->only(['create', 'store']);
        $this->middleware('permission:quotes.edit')->only(['edit', 'update']);
        $this->middleware('permission:quotes.delete')->only('destroy');
        $this->middleware('permission:quotes.send')->only('send');
        $this->middleware('permission:quotes.convert')->only('convert');
    }

    /**
     * Liste des devis
     */
    public function index(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $query = Quote::where('company_id', $companyId)
            ->with(['client', 'user']);

        // Filtrage par site pour les utilisateurs non-admins
        if (!$user->hasAccessToAllSites()) {
            $query->whereIn('site_id', $user->getSiteIds());
        }

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filtre par période
        if ($from = $request->input('date_from')) {
            $query->whereDate('quote_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('quote_date', '<=', $to);
        }

        // Tri dynamique (par défaut : numéro de devis décroissant)
        $sortable = ['quote_number', 'client_name', 'quote_date', 'valid_until', 'total_amount', 'status'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'quote_number';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $quotes = $query->paginate(15)->withQueryString();

        // Statistiques (scope identique au filtrage ci-dessus)
        $statsQuery = Quote::where('company_id', $companyId);
        if (!$user->hasAccessToAllSites()) {
            $statsQuery->whereIn('site_id', $user->getSiteIds());
        }
        $total    = (clone $statsQuery)->count();
        $accepted = (clone $statsQuery)->where('status', 'accepted')->count();
        $stats = [
            'total'           => $total,
            'pending_count'   => (clone $statsQuery)->whereIn('status', ['draft', 'sent'])->count(),
            'accepted_count'  => $accepted,
            'conversion_rate' => $total > 0 ? round($accepted / $total * 100, 1) : 0,
        ];

        return view('quotes.index', compact('quotes', 'stats'));
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

        // Client présélectionné
        $selectedClient = null;
        if ($user->hasFeature('save_clients') && $clientId = $request->input('client_id')) {
            $selectedClient = Client::find($clientId);
        }

        return view('quotes.create', compact(
            'clients', 'products', 'taxes', 'sites', 'selectedClient'
        ));
    }

    /**
     * Enregistrer un nouveau devis
     */
    public function store(StoreQuoteRequest $request): RedirectResponse
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

            // Données du devis
            $quoteData = [
                'company_id'     => $user->company_id,
                'site_id'        => $request->site_id ?? $user->getPrimarySite()?->id,
                'user_id'        => $user->id,
                'client_id'      => $request->client_id,
                'quote_date'     => $request->quote_date ?? now(),
                'valid_until'    => $request->valid_until ?? now()->addDays(30),
                'status'         => 'draft',
                'discount_type'  => $discountType,
                'discount_value' => $discountValue,
                'promo_id'       => $promoModel?->id,
                'promo_code'     => $promoModel ? $promoModel->code : null,
                'notes'          => $request->notes,
                'terms'          => $request->terms,
                'subject'        => $request->subject,
            ];

            // Infos client
            if ($client = Client::find($request->client_id)) {
                $quoteData = array_merge($quoteData, [
                    'client_name'    => $client->display_name,
                    'client_email'   => $client->email,
                    'client_phone'   => $client->phone,
                    'client_address' => $client->full_address,
                ]);
            } else {
                $quoteData['client_name']    = $request->client_name;
                $quoteData['client_email']   = $request->client_email;
                $quoteData['client_phone']   = $request->client_phone;
                $quoteData['client_address'] = $request->client_address;
            }

            $quote = Quote::create($quoteData);

            // Ajouter les lignes
            foreach ($request->items as $index => $item) {
                $quote->items()->create([
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
                        // Calculer la base imposable (avant remise globale)
                        $taxableBase = $quote->items()
                            ->when($tax->apply_to === 'products', fn($q) => $q->where('type', 'product'))
                            ->when($tax->apply_to === 'services', fn($q) => $q->where('type', 'service'))
                            ->sum('total');

                        $quote->taxes()->create([
                            'tax_id'        => $tax->id,
                            'tax_name'      => $tax->name,
                            'tax_rate'      => $tax->rate,
                            'apply_to'      => $tax->apply_to,
                            'taxable_base'  => $taxableBase,
                            'tax_amount'    => round($taxableBase * ($tax->rate / 100), 2),
                        ]);
                    }
                }
            }

            // Recalculer les totaux (proratise les taxes par rapport à la remise globale)
            $quote->calculateTotals();

            // Incrémenter le compteur de la promo si utilisée
            $promoModel?->increment('uses_count');

            DB::commit();

            return redirect()
                ->route('quotes.show', $quote)
                ->with('success', 'Devis créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du devis: ' . $e->getMessage());
        }
    }

    /**
     * Afficher un devis
     */
    public function show(Request $request, Quote $quote): View
    {
        $this->authorizeCompany($request, $quote);

        $quote->load(['client', 'user', 'site', 'items.product', 'taxes', 'convertedInvoice']);

        return view('quotes.show', compact('quote'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Quote $quote): View|RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        if (!$quote->canBeEdited()) {
            return redirect()
                ->route('quotes.show', $quote)
                ->with('error', 'Ce devis ne peut plus être modifié.');
        }

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

        $quote->load(['items', 'taxes']);
        $selectedTaxes = $quote->taxes->pluck('tax_id')->toArray();

        return view('quotes.edit', compact(
            'quote', 'clients', 'products', 'taxes', 'sites', 'selectedTaxes'
        ));
    }

    /**
     * Mettre à jour un devis
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        if (!$quote->canBeEdited()) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        try {
            DB::beginTransaction();

            // Promo code handling for update
            $discountType  = $request->discount_type ?: null;
            $discountValue = $discountType ? ($request->discount_value ?? 0) : 0;
            $promoModel    = null;
            $newPromoCode  = $request->filled('promo_code') ? strtoupper($request->promo_code) : null;

            if ($newPromoCode) {
                $promoModel = Promotion::where('company_id', $quote->company_id)
                    ->where('code', $newPromoCode)
                    ->first();
            }

            // Mettre à jour les infos générales
            $newStatus = $request->status ?? $quote->status;
            $updateData = [
                'client_id'      => $request->client_id,
                'site_id'        => $request->site_id,
                'quote_date'     => $request->quote_date,
                'valid_until'    => $request->valid_until,
                'discount_type'  => $discountType,
                'discount_value' => $discountValue,
                'promo_id'       => $promoModel?->id,
                'promo_code'     => $promoModel ? $promoModel->code : null,
                'notes'          => $request->notes,
                'terms'          => $request->terms,
                'subject'        => $request->subject,
                'status'         => $newStatus,
            ];

            // Enregistrer la date d'envoi si le statut passe à "envoyé"
            if ($newStatus === 'sent' && $quote->status === 'draft') {
                $updateData['sent_at'] = now();
            }

            $quote->update($updateData);

            // Mettre à jour les infos client
            if ($client = Client::find($request->client_id)) {
                $quote->update([
                    'client_name'    => $client->display_name,
                    'client_email'   => $client->email,
                    'client_phone'   => $client->phone,
                    'client_address' => $client->full_address,
                ]);
            } else {
                $quote->update([
                    'client_name'    => $request->client_name,
                    'client_email'   => $request->client_email,
                    'client_phone'   => $request->client_phone,
                    'client_address' => $request->client_address,
                ]);
            }

            // Supprimer et recréer les lignes
            $quote->items()->delete();
            foreach ($request->items as $index => $item) {
                $quote->items()->create([
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

            // Supprimer et recréer les taxes
            $quote->taxes()->delete();
            if ($request->filled('taxes')) {
                foreach ($request->taxes as $taxId) {
                    $tax = Tax::find($taxId);
                    if ($tax) {
                        $taxableBase = $quote->items()
                            ->when($tax->apply_to === 'products', fn($q) => $q->where('type', 'product'))
                            ->when($tax->apply_to === 'services', fn($q) => $q->where('type', 'service'))
                            ->sum('total');

                        $quote->taxes()->create([
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

            $quote->calculateTotals();

            // Incrémenter le compteur promo si c'est un nouveau code
            if ($promoModel && $newPromoCode !== $quote->getOriginal('promo_code')) {
                $promoModel->increment('uses_count');
            }

            DB::commit();

            return redirect()
                ->route('quotes.show', $quote)
                ->with('success', 'Devis mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Mettre en corbeille un devis
     */
    public function destroy(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        // Super admin peut tout mettre en corbeille
        if (!$request->user()->hasRole('company_admin') && !$quote->canBeDeleted()) {
            return back()->with('error', 'Ce devis ne peut pas être supprimé.');
        }

        $quote->delete();

        return redirect()
            ->route('quotes.index')
            ->with('success', 'Devis mis en corbeille.');
    }

    /**
     * Corbeille des devis
     */
    public function trash(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $quotes = Quote::onlyTrashed()
            ->where('company_id', $companyId)
            ->with(['client', 'user'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('quotes.trash', compact('quotes'));
    }

    /**
     * Restaurer un devis
     */
    public function restore(Request $request, int $id): RedirectResponse
    {
        $quote = Quote::onlyTrashed()->where('company_id', $request->user()->company_id)->findOrFail($id);
        $quote->restore();

        return redirect()->route('quotes.trash')->with('success', 'Devis restauré avec succès.');
    }

    /**
     * Supprimer définitivement un devis
     */
    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $quote = Quote::onlyTrashed()->where('company_id', $request->user()->company_id)->findOrFail($id);
        $quote->forceDelete();

        return redirect()->route('quotes.trash')->with('success', 'Devis supprimé définitivement.');
    }

    /**
     * Télécharger le PDF
     */
    public function pdf(Request $request, Quote $quote, PdfService $pdfService)
    {
        $this->authorizeCompany($request, $quote);

        $quote->load(['company', 'client', 'items', 'taxes']);

        return $pdfService->download(
            'pdf.quote',
            [
                'quote' => $quote,
                'company' => $quote->company,
                'style' => \App\Models\DocumentStyle::forDocument($quote->company_id, 'quote'),
            ],
            'devis-' . $quote->quote_number . '.pdf'
        );
    }

    /**
     * Marquer comme envoyé
     */
    public function send(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        $quote->markAsSent();

        return back()->with('success', 'Devis marqué comme envoyé.');
    }

    /**
     * Marquer comme accepté
     */
    public function accept(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        $quote->markAsAccepted();

        return back()->with('success', 'Devis marqué comme accepté.');
    }

    /**
     * Marquer comme refusé
     */
    public function decline(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        $quote->markAsDeclined();

        return back()->with('success', 'Devis marqué comme refusé.');
    }

    /**
     * Mettre à jour le statut du devis (action dédiée)
     */
    public function updateStatus(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        if ($quote->isConverted()) {
            return back()->with('error', 'Le statut d\'un devis converti en facture ne peut pas être modifié.');
        }

        $request->validate([
            'status' => ['required', 'in:draft,sent,accepted,declined,expired'],
        ]);

        $newStatus = $request->status;
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'sent' && !$quote->sent_at) {
            $updateData['sent_at'] = now();
        }

        $quote->update($updateData);

        $labels = [
            'draft'    => 'Brouillon',
            'sent'     => 'Envoyé',
            'accepted' => 'Accepté',
            'declined' => 'Refusé',
            'expired'  => 'Expiré',
        ];

        return back()->with('success', 'Statut mis à jour : ' . ($labels[$newStatus] ?? $newStatus) . '.');
    }

    /**
     * Convertir en facture
     */
    public function convert(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        if (!$quote->canBeConverted()) {
            return back()->with('error', 'Ce devis ne peut pas être converti en facture.');
        }

        try {
            $invoice = Invoice::createFromQuote($quote);

            // Vérifier les stocks pour les articles de type produit
            $stockIssues = [];
            $invoice->loadMissing('items');
            foreach ($invoice->items as $item) {
                if (!$item->product_id || $item->type !== 'product') {
                    continue;
                }
                $product = \App\Models\Product::find($item->product_id);
                if (!$product || !$product->track_inventory) {
                    continue;
                }
                if ((float) $item->quantity > (float) $product->stock_quantity) {
                    $stockIssues[] = "« {$item->description} » : qté {$item->quantity} > stock {$product->stock_quantity}";
                }
            }

            if (!empty($stockIssues)) {
                $warning = 'Stock insuffisant pour : ' . implode(', ', $stockIssues) . '. Corrigez les lignes en rouge avant d\'envoyer la facture.';
                return redirect()
                    ->route('invoices.edit', $invoice)
                    ->with('warning', $warning);
            }

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Facture créée avec succès à partir du devis.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la conversion: ' . $e->getMessage());
        }
    }

    /**
     * Dupliquer un devis
     */
    public function duplicate(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeCompany($request, $quote);

        try {
            DB::beginTransaction();

            $newQuote = $quote->replicate(['quote_number', 'status', 'sent_at', 'viewed_at', 
                'accepted_at', 'declined_at', 'converted_to_invoice_id', 'pdf_path', 'pdf_generated_at']);
            
            $newQuote->quote_date = now();
            $newQuote->valid_until = now()->addDays(30);
            $newQuote->status = 'draft';
            $newQuote->save();

            // Dupliquer les lignes
            foreach ($quote->items as $item) {
                $newItem = $item->replicate(['quote_id']);
                $newItem->quote_id = $newQuote->id;
                $newItem->save();
            }

            // Dupliquer les taxes
            foreach ($quote->taxes as $tax) {
                $newTax = $tax->replicate(['quote_id']);
                $newTax->quote_id = $newQuote->id;
                $newTax->save();
            }

            $newQuote->calculateTotals();

            DB::commit();

            return redirect()
                ->route('quotes.edit', $newQuote)
                ->with('success', 'Devis dupliqué. Modifiez les informations nécessaires.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la duplication.');
        }
    }

    /**
     * Vérifier que le devis appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Quote $quote): void
    {
        if ($quote->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à ce devis.');
        }
    }
}
