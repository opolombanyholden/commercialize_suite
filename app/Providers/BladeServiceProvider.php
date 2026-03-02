<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Directive @feature('feature_name') - vérifie si l'utilisateur a accès à une feature
        Blade::directive('feature', function ($expression) {
            return "<?php if (auth()->check() && auth()->user()->hasFeature({$expression})): ?>";
        });

        Blade::directive('endfeature', function () {
            return '<?php endif; ?>';
        });

        // Directive @version('pro') - vérifie si l'utilisateur a la version requise ou supérieure
        Blade::directive('version', function ($expression) {
            return "<?php if (auth()->check() && auth()->user()->hasVersionOrHigher({$expression})): ?>";
        });

        Blade::directive('endversion', function () {
            return '<?php endif; ?>';
        });

        // Directive @superadmin
        Blade::directive('superadmin', function () {
            return "<?php if (auth()->check() && auth()->user()->isSuperAdmin()): ?>";
        });

        Blade::directive('endsuperadmin', function () {
            return '<?php endif; ?>';
        });

        // Directive @companyadmin
        Blade::directive('companyadmin', function () {
            return "<?php if (auth()->check() && auth()->user()->isCompanyAdmin()): ?>";
        });

        Blade::directive('endcompanyadmin', function () {
            return '<?php endif; ?>';
        });
    }
}
