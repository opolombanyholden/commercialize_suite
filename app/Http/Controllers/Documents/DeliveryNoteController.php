<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:deliveries.view')->only(['index', 'show']);
        $this->middleware('permission:deliveries.create')->only(['create', 'store', 'createFromInvoice']);
        $this->middleware('permission:deliveries.edit')->only(['edit', 'update', 'updateStatus', 'saveSignature', 'verifyPin']);
        $this->middleware('permission:deliveries.delete')->only('destroy');
    }

    /**
     * Liste des bons de livraison
     */
    public function index(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $query = DeliveryNote::where('company_id', $companyId)
            ->with(['client', 'invoice']);

        // Filtrage par site pour les utilisateurs non-admins
        if (!$user->hasAccessToAllSites()) {
            $query->whereIn('site_id', $user->getSiteIds());
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('planned_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('planned_date', '<=', $to);
        }

        // Tri dynamique (par défaut : numéro de BL décroissant)
        $sortable = ['delivery_number', 'client_name', 'planned_date', 'status'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'delivery_number';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $deliveries = $query->paginate(15)->withQueryString();

        $statsQuery = DeliveryNote::where('company_id', $companyId);
        if (!$user->hasAccessToAllSites()) {
            $statsQuery->whereIn('site_id', $user->getSiteIds());
        }
        $stats = [
            'pending'    => (clone $statsQuery)->where('status', 'pending')->count(),
            'in_transit' => (clone $statsQuery)->where('status', 'in_transit')->count(),
            'delivered'  => (clone $statsQuery)->where('status', 'delivered')->count(),
            'total'      => (clone $statsQuery)->count(),
        ];

        return view('deliveries.index', compact('deliveries', 'stats'));
    }

    /**
     * Formulaire de création manuelle
     */
    public function create(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $clients  = Client::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $sitesQuery = Site::where('company_id', $companyId)->where('is_active', true);
        if (!$user->hasAccessToAllSites()) {
            $sitesQuery->whereIn('id', $user->getSiteIds());
        }
        $sites = $sitesQuery->ordered()->get();

        $selectedClient  = null;
        $fromInvoice     = null;

        if ($clientId = $request->input('client_id')) {
            $selectedClient = Client::find($clientId);
        }
        if ($invoiceId = $request->input('invoice_id')) {
            $fromInvoice = Invoice::with('items.product')
                ->where('company_id', $companyId)
                ->find($invoiceId);
        }

        return view('deliveries.create', compact('clients', 'products', 'sites', 'selectedClient', 'fromInvoice'));
    }

    /**
     * Créer directement depuis une facture (raccourci)
     */
    public function createFromInvoice(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->company_id !== $request->user()->company_id) {
            abort(403);
        }

        // Bloquer si la facture est entièrement livrée (sans retours non résolus)
        $invoice->loadMissing('items');
        if ($invoice->isFullyDelivered()) {
            return back()->with('error', 'Toutes les quantités de cette facture ont déjà été livrées. Créez un retour client pour débloquer une nouvelle livraison.');
        }

        try {
            $dn = DeliveryNote::createFromInvoice($invoice);

            return redirect()
                ->route('deliveries.edit', $dn)
                ->with('success', 'Bon de livraison créé depuis la facture ' . $invoice->invoice_number . '. Vérifiez et enregistrez.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un nouveau bon de livraison
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'client_name'  => ['required', 'string', 'max:255'],
            'planned_date' => ['required', 'date'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
        ]);

        // Validate quantities don't exceed remaining when linked to an invoice
        if ($invoiceId = ($request->invoice_id ?: null)) {
            $invoice = Invoice::with('items')
                ->where('company_id', $user->company_id)
                ->find($invoiceId);
            if ($invoice) {
                $alreadyDelivered = DeliveryNote::netDeliveredQtiesForInvoice($invoice->id);
                $orderedQtys = [];
                foreach ($invoice->items as $invItem) {
                    $key = DeliveryNote::itemKey($invItem->product_id, $invItem->description);
                    $orderedQtys[$key] = (float) $invItem->quantity;
                }
                foreach ($request->items as $item) {
                    $key = DeliveryNote::itemKey($item['product_id'] ?? null, $item['description']);
                    if (isset($orderedQtys[$key])) {
                        $maxQty = $orderedQtys[$key] - ($alreadyDelivered[$key] ?? 0);
                        if ((float)$item['quantity'] > $maxQty + 0.001) {
                            return back()->withInput()->with('error', sprintf(
                                'La quantité pour "%s" (%s) dépasse la quantité restante à livrer (max : %s).',
                                $item['description'],
                                number_format((float)$item['quantity'], 2),
                                number_format($maxQty, 2)
                            ));
                        }
                    }
                }
            }
        }

        $deliveryImmediate = $request->boolean('delivery_immediate');

        try {
            DB::beginTransaction();

            $siteId = $request->site_id ?? $user->getPrimarySite()?->id;

            $dn = DeliveryNote::create([
                'company_id'       => $user->company_id,
                'site_id'          => $siteId,
                'user_id'          => $user->id,
                'invoice_id'       => $request->invoice_id ?: null,
                'client_id'        => $request->client_id ?: null,
                'client_name'      => $request->client_name,
                'client_email'     => $request->client_email,
                'client_phone'     => $request->client_phone,
                'client_address'   => $request->client_address,
                'delivery_address' => $request->delivery_address ?: $request->client_address,
                'planned_date'     => $request->planned_date,
                'delivered_date'   => $deliveryImmediate ? now()->toDateString() : null,
                'livreur'          => $request->livreur,
                'notes'            => $request->notes,
                'status'           => $deliveryImmediate ? 'delivered' : 'pending',
            ]);

            foreach ($request->items as $index => $item) {
                $dn->items()->create([
                    'product_id'  => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit'        => $item['unit'] ?? null,
                    'notes'       => $item['notes'] ?? null,
                    'sort_order'  => $index,
                ]);

                // Décrémenter le stock si livraison immédiate
                if ($deliveryImmediate && !empty($item['product_id'])) {
                    $this->decrementStock($item['product_id'], (float) $item['quantity'], $user, $siteId, $dn->delivery_number);
                }
            }

            DB::commit();

            $msg = $deliveryImmediate
                ? 'Bon de livraison créé et marqué comme livré. Le stock a été mis à jour.'
                : 'Bon de livraison créé avec succès.';

            return redirect()
                ->route('deliveries.show', $dn)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un bon de livraison
     */
    public function show(Request $request, DeliveryNote $delivery): View
    {
        $this->authorizeCompany($request, $delivery);
        $delivery->load(['company', 'client', 'user', 'site', 'items.product', 'invoice.items']);

        return view('deliveries.show', compact('delivery'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, DeliveryNote $delivery): View|RedirectResponse
    {
        $this->authorizeCompany($request, $delivery);

        if ($delivery->isDelivered()) {
            return redirect()
                ->route('deliveries.show', $delivery)
                ->with('error', 'Un bon de livraison livré ne peut plus être modifié.');
        }

        $user      = $request->user();
        $companyId = $user->company_id;
        $clients   = Client::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $sitesQuery = Site::where('company_id', $companyId)->where('is_active', true);
        if (!$user->hasAccessToAllSites()) {
            $sitesQuery->whereIn('id', $user->getSiteIds());
        }
        $sites = $sitesQuery->ordered()->get();

        $delivery->load(['items.product', 'invoice.items']);

        // Compute max deliverable qty per item index when linked to an invoice
        $itemMaxQtys = [];
        if ($delivery->invoice_id && $delivery->invoice) {
            $alreadyDelivered = DeliveryNote::deliveredQtiesForInvoice($delivery->invoice_id, $delivery->id);
            $orderedQtys = [];
            foreach ($delivery->invoice->items as $invItem) {
                $key = DeliveryNote::itemKey($invItem->product_id, $invItem->description);
                $orderedQtys[$key] = (float) $invItem->quantity;
            }
            foreach ($delivery->items->values() as $idx => $dnItem) {
                $key = DeliveryNote::itemKey($dnItem->product_id, $dnItem->description);
                $ordered = $orderedQtys[$key] ?? null;
                $prevDel = $alreadyDelivered[$key] ?? 0;
                $itemMaxQtys[$idx] = $ordered !== null ? max(0, $ordered - $prevDel) : null;
            }
        }

        return view('deliveries.edit', compact('delivery', 'clients', 'products', 'sites', 'itemMaxQtys'));
    }

    /**
     * Mettre à jour un bon de livraison
     */
    public function update(Request $request, DeliveryNote $delivery): RedirectResponse
    {
        $this->authorizeCompany($request, $delivery);

        if ($delivery->isDelivered()) {
            return back()->with('error', 'Un bon de livraison livré ne peut plus être modifié.');
        }

        $request->validate([
            'client_name'  => ['required', 'string', 'max:255'],
            'planned_date' => ['required', 'date'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
        ]);

        // Validate quantities don't exceed remaining when linked to an invoice
        if ($delivery->invoice_id) {
            $invoice = Invoice::with('items')
                ->where('company_id', $request->user()->company_id)
                ->find($delivery->invoice_id);
            if ($invoice) {
                $alreadyDelivered = DeliveryNote::netDeliveredQtiesForInvoice($invoice->id, $delivery->id);
                $orderedQtys = [];
                foreach ($invoice->items as $invItem) {
                    $key = DeliveryNote::itemKey($invItem->product_id, $invItem->description);
                    $orderedQtys[$key] = (float) $invItem->quantity;
                }
                foreach ($request->items as $item) {
                    $key = DeliveryNote::itemKey($item['product_id'] ?? null, $item['description']);
                    if (isset($orderedQtys[$key])) {
                        $maxQty = $orderedQtys[$key] - ($alreadyDelivered[$key] ?? 0);
                        if ((float)$item['quantity'] > $maxQty + 0.001) {
                            return back()->withInput()->with('error', sprintf(
                                'La quantité pour "%s" (%s) dépasse la quantité restante à livrer (max : %s).',
                                $item['description'],
                                number_format((float)$item['quantity'], 2),
                                number_format($maxQty, 2)
                            ));
                        }
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            $delivery->update([
                'client_id'        => $request->client_id ?: null,
                'client_name'      => $request->client_name,
                'client_email'     => $request->client_email,
                'client_phone'     => $request->client_phone,
                'client_address'   => $request->client_address,
                'delivery_address' => $request->delivery_address ?: $request->client_address,
                'planned_date'     => $request->planned_date,
                'livreur'          => $request->livreur,
                'notes'            => $request->notes,
                'site_id'          => $request->site_id,
            ]);

            $delivery->items()->delete();
            foreach ($request->items as $index => $item) {
                $delivery->items()->create([
                    'product_id'  => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit'        => $item['unit'] ?? null,
                    'notes'       => $item['notes'] ?? null,
                    'sort_order'  => $index,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('deliveries.show', $delivery)
                ->with('success', 'Bon de livraison mis à jour.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Mettre en corbeille un bon de livraison
     */
    public function destroy(Request $request, DeliveryNote $delivery): RedirectResponse
    {
        $this->authorizeCompany($request, $delivery);

        // Super admin peut tout mettre en corbeille
        if (!$request->user()->hasRole('company_admin') && $delivery->isDelivered()) {
            return back()->with('error', 'Impossible de supprimer un bon de livraison livré.');
        }

        $delivery->delete();

        return redirect()
            ->route('deliveries.index')
            ->with('success', 'Bon de livraison mis en corbeille.');
    }

    /**
     * Corbeille des bons de livraison
     */
    public function trash(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $deliveries = DeliveryNote::onlyTrashed()
            ->where('company_id', $companyId)
            ->with(['client', 'invoice'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('deliveries.trash', compact('deliveries'));
    }

    /**
     * Restaurer un bon de livraison
     */
    public function restore(Request $request, int $id): RedirectResponse
    {
        $dn = DeliveryNote::onlyTrashed()->where('company_id', $request->user()->company_id)->findOrFail($id);
        $dn->restore();

        return redirect()->route('deliveries.trash')->with('success', 'Bon de livraison restauré.');
    }

    /**
     * Supprimer définitivement un bon de livraison
     */
    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $dn = DeliveryNote::onlyTrashed()->where('company_id', $request->user()->company_id)->findOrFail($id);
        $dn->forceDelete();

        return redirect()->route('deliveries.trash')->with('success', 'Bon de livraison supprimé définitivement.');
    }

    /**
     * Mettre à jour le statut
     */
    public function updateStatus(Request $request, DeliveryNote $delivery): RedirectResponse
    {
        $this->authorizeCompany($request, $delivery);

        $request->validate(['status' => ['required', 'in:pending,in_transit,delivered,cancelled']]);

        $newStatus   = $request->status;
        $oldStatus   = $delivery->status;
        $user        = $request->user();
        $updateData  = ['status' => $newStatus];

        if ($newStatus === 'delivered' && !$delivery->delivered_date) {
            $updateData['delivered_date'] = now()->toDateString();
        }

        // Passage à "livré" depuis un statut non-livré → décrémenter le stock
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $delivery->loadMissing('items');
            foreach ($delivery->items as $item) {
                if ($item->product_id) {
                    $this->decrementStock($item->product_id, (float) $item->quantity, $user, $delivery->site_id, $delivery->delivery_number);
                }
            }
        }

        // Annulation d'une livraison déjà livrée → restaurer le stock
        if ($newStatus === 'cancelled' && $oldStatus === 'delivered') {
            $delivery->loadMissing('items');
            foreach ($delivery->items as $item) {
                if ($item->product_id) {
                    $this->incrementStock($item->product_id, (float) $item->quantity, $user, $delivery->site_id, $delivery->delivery_number);
                }
            }
        }

        $delivery->update($updateData);

        $labels = [
            'pending'    => 'En attente',
            'in_transit' => 'En transit',
            'delivered'  => 'Livré',
            'cancelled'  => 'Annulé',
        ];

        $msg = 'Statut mis à jour : ' . ($labels[$newStatus] ?? $newStatus) . '.';
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $msg .= ' Le stock a été mis à jour.';
        }
        if ($newStatus === 'cancelled' && $oldStatus === 'delivered') {
            $msg .= ' Le stock a été restauré.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Générer le PDF
     */
    public function pdf(Request $request, DeliveryNote $delivery, PdfService $pdfService)
    {
        $this->authorizeCompany($request, $delivery);
        $delivery->load(['company', 'client', 'items.product', 'invoice.items']);

        return $pdfService->download(
            'pdf.delivery-note',
            [
                'delivery' => $delivery,
                'company' => $delivery->company,
                'style' => \App\Models\DocumentStyle::forDocument($delivery->company_id, 'delivery_note'),
            ],
            'bon-livraison-' . $delivery->delivery_number . '.pdf'
        );
    }

    /**
     * Vérifier le PIN de livraison (saisi par le livreur)
     */
    public function verifyPin(Request $request, DeliveryNote $delivery): RedirectResponse
    {
        $this->authorizeCompany($request, $delivery);

        $request->validate(['pin' => ['required', 'string']]);

        $delivery->loadMissing('invoice');

        if (!$delivery->invoice || !$delivery->invoice->hasDeliveryPin()) {
            return back()->with('error', 'Cette facture n\'a pas de code PIN de livraison.');
        }

        if (strtoupper(trim($request->pin)) !== $delivery->invoice->delivery_pin) {
            return back()->withErrors(['pin' => 'Code incorrect. Veuillez réessayer.']);
        }

        $wasDelivered = $delivery->status === 'delivered';

        $delivery->update([
            'pin_verified'    => true,
            'pin_verified_at' => now(),
            'pin_verified_by' => 'livreur',
            'status'          => 'delivered',
            'delivered_date'  => today(),
        ]);

        // Décrémenter le stock si pas encore livré
        if (!$wasDelivered) {
            $user = $request->user();
            $delivery->loadMissing('items');
            foreach ($delivery->items as $item) {
                if ($item->product_id) {
                    $this->decrementStock($item->product_id, (float) $item->quantity, $user, $delivery->site_id, $delivery->delivery_number);
                }
            }
        }

        return back()->with('success', 'PIN correct. Bon de livraison marqué comme livré.');
    }

    /**
     * Enregistrer la signature
     */
    public function saveSignature(Request $request, DeliveryNote $delivery): RedirectResponse
    {
        $this->authorizeCompany($request, $delivery);

        $request->validate(['signature' => ['required', 'string']]);

        $wasDelivered = $delivery->status === 'delivered';

        $delivery->update([
            'signature' => $request->signature,
            'status'    => 'delivered',
            'delivered_date' => $delivery->delivered_date ?? now()->toDateString(),
        ]);

        // Décrémenter le stock si pas encore livré
        if (!$wasDelivered) {
            $user = $request->user();
            $delivery->loadMissing('items');
            foreach ($delivery->items as $item) {
                if ($item->product_id) {
                    $this->decrementStock($item->product_id, (float) $item->quantity, $user, $delivery->site_id, $delivery->delivery_number);
                }
            }
        }

        return back()->with('success', 'Signature enregistrée. Bon de livraison marqué comme livré.');
    }

    /**
     * Décrémenter le stock d'un produit et enregistrer le mouvement
     */
    protected function decrementStock(int $productId, float $quantity, $user, ?int $siteId, string $reference): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->track_inventory) {
            return;
        }

        $stockBefore = $product->stock_quantity;
        $product->decrementStock($quantity);
        $product->refresh();

        StockMovement::create([
            'company_id'   => $user->company_id,
            'product_id'   => $product->id,
            'site_id'      => $siteId,
            'user_id'      => $user->id,
            'type'         => 'sale',
            'quantity'     => -abs((int) $quantity),
            'stock_before' => $stockBefore,
            'stock_after'  => $product->stock_quantity,
            'reference'    => $reference,
            'reason'       => 'Livraison',
        ]);
    }

    /**
     * Restaurer le stock d'un produit (annulation livraison)
     */
    protected function incrementStock(int $productId, float $quantity, $user, ?int $siteId, string $reference): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->track_inventory) {
            return;
        }

        $stockBefore = $product->stock_quantity;
        $product->incrementStock($quantity);
        $product->refresh();

        StockMovement::create([
            'company_id'   => $user->company_id,
            'product_id'   => $product->id,
            'site_id'      => $siteId,
            'user_id'      => $user->id,
            'type'         => 'return',
            'quantity'     => abs((int) $quantity),
            'stock_before' => $stockBefore,
            'stock_after'  => $product->stock_quantity,
            'reference'    => $reference,
            'reason'       => 'Annulation livraison',
        ]);
    }

    protected function authorizeCompany(Request $request, DeliveryNote $delivery): void
    {
        if ($delivery->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
