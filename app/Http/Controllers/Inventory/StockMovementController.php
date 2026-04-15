<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only('index', 'show');
        $this->middleware('permission:products.edit')->only('create', 'store');
    }

    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = StockMovement::where('company_id', $companyId)
            ->with(['product', 'site', 'user']);

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }
        if ($siteId = $request->input('site_id')) {
            $query->where('site_id', $siteId);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $movements = $query->latest()->paginate(25)->withQueryString();

        $warehouses = Site::where('company_id', $companyId)->where('is_warehouse', true)->orderBy('name')->get();
        $products   = Product::where('company_id', $companyId)->active()->orderBy('name')->get();

        $types = [
            'in'         => 'Entrée de stock',
            'out'        => 'Sortie manuelle',
            'sale'       => 'Vente',
            'return'     => 'Retour client',
            'adjustment' => 'Ajustement',
            'inventory'  => 'Inventaire',
            'loss'       => 'Perte / Casse',
        ];

        return view('inventory.movements.index', compact('movements', 'warehouses', 'products', 'types'));
    }

    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $products   = Product::where('company_id', $companyId)
            ->active()
            ->where('type', 'product')
            ->where('track_inventory', true)
            ->orderBy('name')
            ->get();
        $warehouses = Site::where('company_id', $companyId)->where('is_warehouse', true)->active()->orderBy('name')->get();

        $selectedProductId = $request->input('product_id');
        $selectedSiteId    = $request->input('site_id');

        $types = [
            'in'         => 'Entrée de stock (réception)',
            'out'        => 'Sortie manuelle',
            'adjustment' => 'Ajustement de stock',
            'loss'       => 'Perte / Casse',
            'return'     => 'Retour client',
        ];

        return view('inventory.movements.create', compact('products', 'warehouses', 'types', 'selectedProductId', 'selectedSiteId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_id'              => ['nullable', 'exists:sites,id'],
            'type'                 => ['required', 'in:in,out,adjustment,loss,return'],
            'reference'            => ['nullable', 'string', 'max:100'],
            'reason'               => ['nullable', 'string', 'max:255'],
            'notes'                => ['nullable', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required'             => 'Au moins un produit est requis.',
            'items.*.product_id.distinct' => 'Un produit ne peut apparaître qu\'une seule fois.',
        ]);

        $companyId = $request->user()->company_id;
        $userId    = $request->user()->id;
        $isOut     = in_array($data['type'], ['out', 'loss']);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($data['items'] as $item) {
                $product = Product::where('company_id', $companyId)->findOrFail($item['product_id']);

                $qty = $isOut
                    ? -abs((int) $item['quantity'])
                    : abs((int) $item['quantity']);

                $stockBefore = $product->stock_quantity;
                $stockAfter  = $stockBefore + $qty;

                StockMovement::create([
                    'company_id'   => $companyId,
                    'product_id'   => $product->id,
                    'site_id'      => $data['site_id'] ?? null,
                    'user_id'      => $userId,
                    'type'         => $data['type'],
                    'quantity'     => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'unit_cost'    => $item['unit_cost'] ?? null,
                    'reference'    => $data['reference'] ?? null,
                    'reason'       => $data['reason'] ?? null,
                    'notes'        => $data['notes'] ?? null,
                ]);

                $product->update(['stock_quantity' => max(0, $stockAfter)]);
                $count++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        return redirect()
            ->route('inventory.movements.index')
            ->with('success', $count > 1
                ? "{$count} mouvements de stock enregistrés avec succès."
                : 'Mouvement de stock enregistré avec succès.');
    }

    public function show(Request $request, StockMovement $movement): View
    {
        if ($movement->company_id !== $request->user()->company_id) {
            abort(403);
        }
        $movement->load(['product', 'site', 'user']);
        return view('inventory.movements.show', compact('movement'));
    }

    public function destroy(Request $request, StockMovement $movement): RedirectResponse
    {
        if ($movement->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $movement->delete();

        return redirect()->route('inventory.movements.index')->with('success', 'Mouvement mis en corbeille.');
    }

    public function trash(Request $request): View
    {
        $movements = StockMovement::onlyTrashed()
            ->where('company_id', $request->user()->company_id)
            ->with(['product', 'user'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('inventory.movements.trash', compact('movements'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $m = StockMovement::onlyTrashed()->where('company_id', $request->user()->company_id)->findOrFail($id);
        $m->restore();

        return redirect()->route('inventory.movements.trash')->with('success', 'Mouvement restauré.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $m = StockMovement::onlyTrashed()->where('company_id', $request->user()->company_id)->findOrFail($id);
        $m->forceDelete();

        return redirect()->route('inventory.movements.trash')->with('success', 'Mouvement supprimé définitivement.');
    }
}
