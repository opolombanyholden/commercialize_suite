<?php

use App\Http\Middleware\AuditLog;
use App\Http\Middleware\CheckFeature;
use App\Http\Middleware\CheckSiteAccess;
use App\Http\Middleware\CheckVersion;
use App\Providers\AuthServiceProvider;
use App\Providers\BladeServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'feature'          => CheckFeature::class,
            'version'          => CheckVersion::class,
            'site.access'      => CheckSiteAccess::class,
            'audit.log'        => AuditLog::class,
            'permission'       => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'             => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withProviders([
        AuthServiceProvider::class,
        BladeServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
