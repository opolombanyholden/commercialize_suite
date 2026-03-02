<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLog
{
    /**
     * Enregistre les actions dans le journal d'activité.
     * Activé uniquement pour les entreprises ayant la feature 'audit_logs'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne logger que les mutations (POST, PUT, PATCH, DELETE)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }

        $user = $request->user();

        if (!$user) {
            return $response;
        }

        // Vérifier que la feature audit_logs est activée
        if (!$user->hasFeature('audit_logs')) {
            return $response;
        }

        // Déterminer l'action à partir de la méthode HTTP
        $action = match ($request->method()) {
            'POST'   => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default  => 'action',
        };

        // Logger l'activité
        try {
            ActivityLog::create([
                'company_id'   => $user->company_id,
                'user_id'      => $user->id,
                'subject_type' => null,
                'subject_id'   => null,
                'action'       => $action,
                'description'  => $request->method() . ' ' . $request->path(),
                'old_values'   => null,
                'new_values'   => null,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Ne pas bloquer la requête en cas d'erreur de log
        }

        return $response;
    }
}
