<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DeliveryNote;
use App\Models\DeliveryReturn;
use App\Models\Invoice;
use App\Models\InvoiceTax;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:returns.view')->only(['index', 'show']);
        $this->middleware('permission:returns.create')->only(['create', 'store']);
        $this->middleware('permission:returns.edit')->only(['markReceived', 'resolve']);
        $this->middleware('permission:returns.delete')->only('destroy');
    }

    /**
     * Liste des retours
     */
    public function index(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $query = DeliveryReturn::where('company_id', $companyId)
            ->with(['invoice', 'deliveryNote', 'client']);

        // Filtrage par site pour les utilisateurs non-admins
        if (!$user->hasAccessToAllSites()) {
            $query->whereIn('site_id', $user->getSiteIds());
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $returns = $query->latest()->paginate(15)->withQueryString();

        // Stats scoped au même périmètre de sites
        $statsQuery = DeliveryReturn::where('company_id', $companyId);
        if (!$user->hasAccessToAllSites()) {
            $statsQuery->whereIn('site_id', $user->getSiteIds());
        }
        $stats = [
            'pending'  => (clone $statsQuery)->where('status', 'pending')->count(),
            'received' => (clone $statsQuery)->where('status', 'received')->count(),
            'resolved' => (clone $statsQuery)->where('status', 'resolved')->count(),
            'total'    => (clone $statsQuery)->count(),
        ];

        return view('returns.index', compact('returns', 'stats'));
    }

    /**
     * Formulaire de création d'un retour
     */
    public function create(Request $request): View|RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $deliveryNote = null;
        $invoice      = null;

        if ($dnId = $request->input('delivery_note_id')) {
            $deliveryNote = DeliveryNote::with(['invoice.items', 'items.product'])
                ->where('company_id', $companyId)
                ->find($dnId);

            if (!$deliveryNote || !$deliveryNote->isDelivered()) {
                return back()->with('error', 'Le bon de livraison doit être au statut "Livré" pour créer un retour.');
            }

            $invoice = $deliveryNote->invoice;
        } elseif ($invId = $request->input('invoice_id')) {
            $invoice = Invoice::with('items.product')
                ->where('company_id', $companyId)
                ->find($invId);
        }

        if (!$invoice) {
            return redirect()->route('returns.index')
                ->with('error', 'Veuillez sélectionner une facture ou un bon de livraison.');
        }

        // Pré-remplir les articles à retourner depuis le BL ou la facture
        $prefillItems = collect();
        if ($deliveryNote) {
            // Articles du BL avec unit_price depuis la facture
            $invoicePrices = [];
            if ($invoice) {
                foreach ($invoice->items as $invItem) {
                    $key = DeliveryNote::itemKey($invItem->product_id, $invItem->description);
                    $invoicePrices[$key] = (float) $invItem->unit_price;
                }
            }
            foreach ($deliveryNote->items as $item) {
                $key = DeliveryNote::itemKey($item->product_id, $item->description);
                $prefillItems->push([
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $invoicePrices[$key] ?? null,
                    'unit'        => $item->unit,
                ]);
            }
        } else {
            // Articles de la facture
            foreach ($invoice->items as $item) {
                $prefillItems->push([
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => (float) $item->unit_price,
                    'unit'        => null,
                ]);
            }
        }

        return view('returns.create', compact('deliveryNote', 'invoice', 'prefillItems'));
    }

    /**
     * Enregistrer un retour
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'invoice_id'         => ['required', 'integer'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.description'       => ['required', 'string'],
            'items.*.quantity_returned' => ['required', 'numeric', 'min:0.01'],
        ]);

        $invoice = Invoice::where('company_id', $user->company_id)->find($request->invoice_id);
        if (!$invoice) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            $deliveryNoteId = $request->delivery_note_id ?: null;

            $ret = DeliveryReturn::create([
                'company_id'       => $user->company_id,
                'site_id'          => $invoice->site_id ?? $user->getPrimarySite()?->id,
                'user_id'          => $user->id,
                'invoice_id'       => $invoice->id,
                'delivery_note_id' => $deliveryNoteId,
                'client_id'        => $invoice->client_id,
                'client_name'      => $invoice->client_name,
                'reason'           => $request->reason,
                'notes'            => $request->notes,
            ]);

            foreach ($request->items as $idx => $item) {
                $ret->items()->create([
                    'product_id'       => $item['product_id'] ?? null,
                    'description'      => $item['description'],
                    'quantity_returned' => $item['quantity_returned'],
                    'unit_price'       => isset($item['unit_price']) && $item['unit_price'] !== '' ? $item['unit_price'] : null,
                    'unit'             => $item['unit'] ?? null,
                    'sort_order'       => $idx,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('returns.show', $ret)
                ->with('success', 'Retour client enregistré avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un retour
     */
    public function show(Request $request, DeliveryReturn $return): View
    {
        $this->authorizeCompany($request, $return);
        $return->load(['invoice.items', 'deliveryNote', 'items.product', 'newDelivery', 'creditNote', 'user']);

        return view('returns.show', compact('return'));
    }

    /**
     * Marquer le retour comme "reçu" (marchandises récupérées)
     */
    public function markReceived(Request $request, DeliveryReturn $return): RedirectResponse
    {
        $this->authorizeCompany($request, $return);

        if (!$return->isPending()) {
            return back()->with('error', 'Ce retour ne peut pas être marqué comme reçu.');
        }

        $return->update(['status' => 'received']);

        return back()->with('success', 'Retour marqué comme reçu. Vous pouvez maintenant le résoudre.');
    }

    /**
     * Résoudre le retour (re-livraison ou avoir)
     */
    public function resolve(Request $request, DeliveryReturn $return): RedirectResponse
    {
        $this->authorizeCompany($request, $return);

        if (!$return->canBeResolved()) {
            return back()->with('error', 'Ce retour doit d\'abord être marqué comme "Reçu" avant d\'être résolu.');
        }

        $request->validate([
            'resolution' => ['required', 'in:re_delivery,credit_note'],
        ]);

        $return->load(['invoice.items', 'items.product']);
        $invoice = $return->invoice;

        try {
            DB::beginTransaction();

            if ($request->resolution === 're_delivery') {
                // Créer un nouveau BL avec les items retournés
                $dn = DeliveryNote::create([
                    'company_id'       => $return->company_id,
                    'site_id'          => $return->site_id,
                    'user_id'          => $request->user()->id,
                    'invoice_id'       => $invoice->id,
                    'client_id'        => $invoice->client_id,
                    'client_name'      => $invoice->client_name,
                    'client_email'     => $invoice->client_email,
                    'client_phone'     => $invoice->client_phone,
                    'client_address'   => $invoice->client_address,
                    'delivery_address' => $invoice->client_address,
                    'planned_date'     => now()->addDay(),
                    'status'           => 'pending',
                    'notes'            => 'Re-livraison suite au retour ' . $return->return_number,
                ]);

                foreach ($return->items as $idx => $rItem) {
                    $dn->items()->create([
                        'product_id'  => $rItem->product_id,
                        'description' => $rItem->description,
                        'quantity'    => $rItem->quantity_returned,
                        'unit'        => $rItem->unit,
                        'sort_order'  => $idx,
                    ]);
                }

                $return->update([
                    'status'          => 'resolved',
                    'resolution'      => 're_delivery',
                    'new_delivery_id' => $dn->id,
                ]);

                DB::commit();

                return redirect()
                    ->route('deliveries.edit', $dn)
                    ->with('success', 'Nouveau bon de livraison créé depuis le retour ' . $return->return_number . '. Vérifiez et enregistrez.');

            } else {
                // Créer un avoir (Invoice type=credit_note)
                $subtotal = $return->items->sum(fn ($i) => $i->quantity_returned * ($i->unit_price ?? 0));

                $creditNote = Invoice::create([
                    'company_id'          => $return->company_id,
                    'site_id'             => $invoice->site_id,
                    'user_id'             => $request->user()->id,
                    'client_id'           => $invoice->client_id,
                    'type'                => 'credit_note',
                    'original_invoice_id' => $invoice->id,
                    'client_name'         => $invoice->client_name,
                    'client_email'        => $invoice->client_email,
                    'client_phone'        => $invoice->client_phone,
                    'client_address'      => $invoice->client_address,
                    'invoice_date'        => now(),
                    'subtotal'            => $subtotal,
                    'tax_amount'          => 0,
                    'total_amount'        => $subtotal,
                    'balance'             => $subtotal,
                    'notes'               => 'Avoir suite au retour ' . $return->return_number . ' — Facture originale : ' . $invoice->invoice_number,
                    'status'              => 'draft',
                    'payment_status'      => 'unpaid',
                ]);

                foreach ($return->items as $idx => $rItem) {
                    $creditNote->items()->create([
                        'product_id'  => $rItem->product_id,
                        'description' => $rItem->description . ' (retour)',
                        'type'        => 'product',
                        'quantity'    => $rItem->quantity_returned,
                        'unit_price'  => $rItem->unit_price ?? 0,
                        'total'       => $rItem->quantity_returned * ($rItem->unit_price ?? 0),
                        'sort_order'  => $idx,
                    ]);
                }

                $return->update([
                    'status'         => 'resolved',
                    'resolution'     => 'credit_note',
                    'credit_note_id' => $creditNote->id,
                ]);

                DB::commit();

                return redirect()
                    ->route('invoices.show', $creditNote)
                    ->with('success', 'Avoir ' . $creditNote->invoice_number . ' créé depuis le retour ' . $return->return_number . '.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la résolution : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un retour (uniquement si pending)
     */
    public function destroy(Request $request, DeliveryReturn $return): RedirectResponse
    {
        $this->authorizeCompany($request, $return);

        if (!$return->isPending()) {
            return back()->with('error', 'Seuls les retours en attente peuvent être supprimés.');
        }

        $return->delete();

        return redirect()->route('returns.index')->with('success', 'Retour supprimé.');
    }

    protected function authorizeCompany(Request $request, DeliveryReturn $return): void
    {
        if ($return->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
