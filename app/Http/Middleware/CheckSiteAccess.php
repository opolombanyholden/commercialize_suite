<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteAccess
{
    /**
     * Vérifie si l'utilisateur a accès au site demandé.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Les super admins ont accès à tout
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier si le site est dans la route
        $site = $request->route('site');

        if ($site) {
            $siteId = is_object($site) ? $site->id : (int) $site;

            // Vérifier l'accès via la table user_site_access
            $hasAccess = $user->sites()->where('site_id', $siteId)->exists()
                || $user->managedSites()->where('id', $siteId)->exists();

            if (!$hasAccess) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Accès non autorisé à ce site.'], 403);
                }

                abort(403, 'Vous n\'avez pas accès à ce site.');
            }
        }

        return $next($request);
    }
}
