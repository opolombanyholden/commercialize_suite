<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryLine;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only('index', 'show');
        $this->middleware('permission:products.edit')->only('create', 'store', 'update', 'complete', 'addLine', 'updateLine');
    }

    public function index(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $query = Inventory::where('company_id', $companyId)
            ->with(['site', 'user'])
            ->withCount('lines');

        // Un entrepôt peut ne pas être rattaché à un site : on filtre uniquement
        // si l'utilisateur n'a pas accès global ET que des sites lui sont attribués.
        if (!$user->hasAccessToAllSites()) {
            $siteIds = $user->getSiteIds();
            // Inclure les inventaires rattachés aux sites accessibles OU sans site
            $query->where(function ($q) use ($siteIds) {
                $q->whereIn('site_id', $siteIds)->orWhereNull('site_id');
            });
        }

        $inventories = $query->latest('date')->paginate(15)->withQueryString();

        return view('inventory.sessions.index', compact('inventories'));
    }

    public function create(Request $request): View
    {
        $user      = $request->user();
        $companyId = $user->company_id;

        $warehousesQuery = Site::where('company_id', $companyId)->where('is_warehouse', true)->active()->orderBy('name');
        if (!$user->hasAccessToAllSites()) {
            $warehousesQuery->whereIn('id', $user->getSiteIds());
        }
        $warehouses = $warehousesQuery->get();

        return view('inventory.sessions.create', compact('warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'date'    => ['required', 'date'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'notes'   => ['nullable', 'string'],
        ]);

        $user      = $request->user();
        $companyId = $user->company_id;

        // Par défaut, rattacher au site principal de l'utilisateur si aucun site choisi
        $siteId = $data['site_id'] ?? $user->getPrimarySite()?->id;

        $inventory = Inventory::create([
            'company_id' => $companyId,
            'user_id'    => $user->id,
            'name'       => $data['name'],
            'date'       => $data['date'],
            'site_id'    => $siteId,
            'status'     => 'in_progress',
            'notes'      => $data['notes'] ?? null,
        ]);

        // Pré-charger les produits avec leur stock actuel
        $products = Product::where('company_id', $companyId)
            ->where('track_inventory', true)
            ->active()
            ->get();

        foreach ($products as $product) {
            InventoryLine::create([
                'inventory_id'      => $inventory->id,
                'product_id'        => $product->id,
                'expected_quantity' => $product->stock_quantity,
            ]);
        }

        return redirect()
            ->route('inventory.sessions.show', $inventory)
            ->with('success', 'Inventaire créé avec succès. Saisissez les quantités comptées.');
    }

    public function show(Request $request, Inventory $session): View
    {
        $this->authorizeInventory($request, $session);

        $session->load(['lines.product', 'site', 'user']);

        return view('inventory.sessions.show', compact('session'));
    }

    public function updateLine(Request $request, Inventory $session, InventoryLine $line): RedirectResponse
    {
        $this->authorizeInventory($request, $session);

        if (!$session->isEditable()) {
            return back()->with('error', 'Cet inventaire est terminé et ne peut plus être modifié.');
        }

        $data = $request->validate([
            'good_quantity'    => ['required', 'integer', 'min:0'],
            'damaged_quantity' => ['required', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $line->update($data);

        return back()->with('success', 'Ligne mise à jour.');
    }

    public function complete(Request $request, Inventory $session): RedirectResponse
    {
        $this->authorizeInventory($request, $session);

        if (!$session->isEditable()) {
            return back()->with('error', 'Cet inventaire est déjà terminé.');
        }

        $session->load('lines.product');
        $companyId = $request->user()->company_id;

        foreach ($session->lines as $line) {
            if ($line->good_quantity === null) {
                continue; // Ligne non comptée, on passe
            }

            $product    = $line->product;
            $newStock   = $line->good_quantity; // Seuls les produits en bon état comptent dans le stock
            $stockBefore = $product->stock_quantity;
            $delta      = $newStock - $stockBefore;

            if ($delta !== 0) {
                StockMovement::create([
                    'company_id'   => $companyId,
                    'product_id'   => $product->id,
                    'site_id'      => $session->site_id,
                    'user_id'      => $request->user()->id,
                    'type'         => 'inventory',
                    'quantity'     => $delta,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $newStock,
                    'reference'    => "INV-{$session->id}",
                    'notes'        => "Inventaire : {$session->name}",
                ]);

                $product->update(['stock_quantity' => $newStock]);
            }
        }

        $session->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('inventory.sessions.show', $session)
            ->with('success', 'Inventaire clôturé. Les stocks ont été mis à jour.');
    }

    protected function authorizeInventory(Request $request, Inventory $session): void
    {
        if ($session->company_id !== $request->user()->company_id) {
            abort(403);
        }
    }
}
