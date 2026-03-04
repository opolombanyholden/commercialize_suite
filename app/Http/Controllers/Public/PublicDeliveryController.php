<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DeliveryNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDeliveryController extends Controller
{
    /**
     * Afficher la page publique d'un bon de livraison (accès par QR code)
     */
    public function show(string $token): View
    {
        $delivery = DeliveryNote::where('public_token', $token)
            ->with(['items.product', 'invoice', 'company'])
            ->firstOrFail();

        return view('public.delivery.show', compact('delivery'));
    }

    /**
     * Vérifier le PIN côté client (page publique)
     */
    public function verify(Request $request, string $token): RedirectResponse
    {
        $delivery = DeliveryNote::where('public_token', $token)
            ->with('invoice')
            ->firstOrFail();

        if ($delivery->isPinVerified()) {
            return redirect()
                ->route('delivery.public', $token)
                ->with('info', 'Cette livraison a déjà été confirmée.');
        }

        $request->validate(['pin' => ['required', 'string']]);

        if (!$delivery->invoice || !$delivery->invoice->hasDeliveryPin()) {
            return redirect()
                ->route('delivery.public', $token)
                ->with('error', 'Cette livraison ne dispose pas d\'un code PIN.');
        }

        if (strtoupper(trim($request->pin)) !== $delivery->invoice->delivery_pin) {
            return redirect()
                ->route('delivery.public', $token)
                ->withErrors(['pin' => 'Code incorrect. Veuillez réessayer.']);
        }

        $delivery->update([
            'pin_verified'    => true,
            'pin_verified_at' => now(),
            'pin_verified_by' => 'client',
            'status'          => 'delivered',
            'delivered_date'  => today(),
        ]);

        return redirect()
            ->route('delivery.public', $token)
            ->with('success', 'Livraison confirmée. Merci !');
    }
}
