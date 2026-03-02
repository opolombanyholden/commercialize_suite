<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $products   = Product::where('company_id', $companyId)->active()->orderBy('name')->get();
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
            'product_id' => ['required', 'exists:products,id'],
            'site_id'    => ['nullable', 'exists:sites,id'],
            'type'       => ['required', 'in:in,out,adjustment,loss,return'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'unit_cost'  => ['nullable', 'numeric', 'min:0'],
            'reference'  => ['nullable', 'string', 'max:100'],
            'reason'     => ['nullable', 'string', 'max:255'],
            'notes'      => ['nullable', 'string'],
        ]);

        $companyId = $request->user()->company_id;
        $product   = Product::where('company_id', $companyId)->findOrFail($data['product_id']);

        // Quantité négative pour les sorties
        $qty = in_array($data['type'], ['out', 'loss'])
            ? -abs((int) $data['quantity'])
            : abs((int) $data['quantity']);

        $stockBefore = $product->stock_quantity;
        $stockAfter  = $stockBefore + $qty;

        StockMovement::create([
            'company_id'   => $companyId,
            'product_id'   => $product->id,
            'site_id'      => $data['site_id'] ?? null,
            'user_id'      => $request->user()->id,
            'type'         => $data['type'],
            'quantity'     => $qty,
            'stock_before' => $stockBefore,
            'stock_after'  => $stockAfter,
            'unit_cost'    => $data['unit_cost'] ?? null,
            'reference'    => $data['reference'] ?? null,
            'reason'       => $data['reason'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ]);

        // Mettre à jour le stock du produit
        $product->update(['stock_quantity' => max(0, $stockAfter)]);

        return redirect()
            ->route('inventory.movements.index')
            ->with('success', 'Mouvement de stock enregistré avec succès.');
    }

    public function show(Request $request, StockMovement $movement): View
    {
        if ($movement->company_id !== $request->user()->company_id) {
            abort(403);
        }
        $movement->load(['product', 'site', 'user']);
        return view('inventory.movements.show', compact('movement'));
    }
}
