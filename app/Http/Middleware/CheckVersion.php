<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVersion
{
    /**
     * Vérifie si l'utilisateur a la version minimale requise.
     * Usage : Route::middleware('version:pro')
     */
    public function handle(Request $request, Closure $next, string $minVersion): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasVersionOrHigher($minVersion)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cette fonctionnalité nécessite la version ' . ucfirst($minVersion) . ' ou supérieure.',
                    'required_version' => $minVersion,
                    'current_version' => $user->version,
                    'upgrade_url' => route('subscription.plans'),
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('warning', 'Cette fonctionnalité nécessite la version ' . ucfirst($minVersion) . ' ou supérieure.');
        }

        return $next($request);
    }
}
