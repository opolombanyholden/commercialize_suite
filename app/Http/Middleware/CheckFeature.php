<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Vérifie si l'utilisateur a accès à la feature requise.
     * Usage : Route::middleware('feature:ecommerce')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cette fonctionnalité n\'est pas disponible dans votre version.',
                    'feature' => $feature,
                    'upgrade_url' => route('subscription.plans'),
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('warning', 'Cette fonctionnalité nécessite une mise à niveau de votre abonnement.');
        }

        return $next($request);
    }
}
