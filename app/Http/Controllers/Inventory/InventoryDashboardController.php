<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $lowStockProducts = Product::where('company_id', $companyId)
            ->lowStock()
            ->take(10)
            ->get();

        $recentMovements = StockMovement::where('company_id', $companyId)
            ->with(['product', 'site', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('inventory.index', compact('lowStockProducts', 'recentMovements'));
    }
}
