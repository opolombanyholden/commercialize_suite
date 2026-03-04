<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:promotions.view')->only(['index']);
        $this->middleware('permission:promotions.create')->only(['create', 'store']);
        $this->middleware('permission:promotions.edit')->only(['edit', 'update']);
        $this->middleware('permission:promotions.delete')->only('destroy');
    }

    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Promotion::where('company_id', $companyId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $promotions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validate($request);

        Promotion::create([
            ...$data,
            'company_id' => $request->user()->company_id,
            'code'       => strtoupper(trim($data['code'])),
        ]);

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promotion créée avec succès.');
    }

    public function edit(Request $request, Promotion $promotion): View|RedirectResponse
    {
        $this->authorizeCompany($request, $promotion);

        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $this->authorizeCompany($request, $promotion);

        $data = $this->validate($request, $promotion);

        $promotion->update([
            ...$data,
            'code' => strtoupper(trim($data['code'])),
        ]);

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promotion mise à jour.');
    }

    public function destroy(Request $request, Promotion $promotion): RedirectResponse
    {
        $this->authorizeCompany($request, $promotion);

        if ($promotion->uses_count > 0) {
            return back()->with('error', 'Impossible de supprimer une promotion déjà utilisée. Désactivez-la à la place.');
        }

        $promotion->delete();

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promotion supprimée.');
    }

    /**
     * Vérifier et retourner les infos d'un code promo (appel AJAX depuis les formulaires).
     */
    public function apply(Request $request): JsonResponse
    {
        $code   = strtoupper(trim($request->input('code', '')));
        $amount = (float) $request->input('amount', 0);

        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Veuillez saisir un code promotionnel.']);
        }

        $companyId = $request->user()->company_id;

        $promo = Promotion::where('company_id', $companyId)
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Code promotionnel introuvable.']);
        }

        if (!$promo->isValid($amount)) {
            return response()->json(['success' => false, 'message' => $promo->getInvalidReason($amount)]);
        }

        $discountAmount = $promo->calculateDiscount($amount);

        return response()->json([
            'success'         => true,
            'name'            => $promo->name,
            'discount_type'   => $promo->discount_type,
            'discount_value'  => (float) $promo->discount_value,
            'discount_amount' => $discountAmount,
        ]);
    }

    // ===== PRIVATE =====

    private function validate(Request $request, ?Promotion $promo = null): array
    {
        $companyId = $request->user()->company_id;
        $promoId   = $promo?->id;

        return $request->validate([
            'code'           => [
                'required', 'string', 'max:50',
                "unique:promotions,code,{$promoId},id,company_id,{$companyId}",
            ],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:500'],
            'discount_type'  => ['required', 'in:percent,amount'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'applies_to'     => ['required', 'in:global,products,services'],
            'min_amount'     => ['nullable', 'numeric', 'min:0'],
            'max_uses'       => ['nullable', 'integer', 'min:1'],
            'valid_from'     => ['nullable', 'date'],
            'valid_until'    => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active'      => ['boolean'],
        ]);
    }

    private function authorizeCompany(Request $request, Promotion $promotion): void
    {
        if ($promotion->company_id !== $request->user()->company_id) {
            abort(403);
        }
    }
}
