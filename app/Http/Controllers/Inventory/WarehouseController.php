<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.edit')->only(['edit', 'update']);
        $this->middleware('permission:products.delete')->only('destroy');
    }

    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $warehouses = Site::where('company_id', $companyId)
            ->where('is_warehouse', true)
            ->withCount(['stockMovements'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('inventory.warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'code'    => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city'    => ['nullable', 'string', 'max:100'],
            'notes'   => ['nullable', 'string'],
        ]);

        $data['company_id']   = $request->user()->company_id;
        $data['is_warehouse'] = true;
        $data['is_active']    = true;

        // Code unique par company
        if (empty($data['code'])) {
            $count = Site::where('company_id', $data['company_id'])->where('is_warehouse', true)->count();
            $data['code'] = 'WH' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $warehouse = Site::create($data);

        return redirect()
            ->route('inventory.warehouses.show', $warehouse)
            ->with('success', 'Entrepôt créé avec succès.');
    }

    public function show(Request $request, Site $warehouse): View
    {
        $this->authorizeWarehouse($request, $warehouse);

        $recentMovements = $warehouse->stockMovements()
            ->with('product', 'user')
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'total_movements' => $warehouse->stockMovements()->count(),
            'total_in'  => $warehouse->stockMovements()->where('quantity', '>', 0)->sum('quantity'),
            'total_out' => $warehouse->stockMovements()->where('quantity', '<', 0)->sum('quantity'),
        ];

        return view('inventory.warehouses.show', compact('warehouse', 'recentMovements', 'stats'));
    }

    public function edit(Request $request, Site $warehouse): View
    {
        $this->authorizeWarehouse($request, $warehouse);
        return view('inventory.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Site $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($request, $warehouse);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['nullable', 'string', 'max:20'],
            'address'   => ['nullable', 'string', 'max:500'],
            'city'      => ['nullable', 'string', 'max:100'],
            'notes'     => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $warehouse->update($data);

        return redirect()
            ->route('inventory.warehouses.show', $warehouse)
            ->with('success', 'Entrepôt mis à jour.');
    }

    public function destroy(Request $request, Site $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($request, $warehouse);

        if ($warehouse->stockMovements()->exists()) {
            return back()->with('error', 'Impossible de supprimer un entrepôt avec des mouvements de stock.');
        }

        $warehouse->delete();

        return redirect()
            ->route('inventory.warehouses.index')
            ->with('success', 'Entrepôt supprimé.');
    }

    protected function authorizeWarehouse(Request $request, Site $warehouse): void
    {
        if ($warehouse->company_id !== $request->user()->company_id || !$warehouse->is_warehouse) {
            abort(403);
        }
    }
}
