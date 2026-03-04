<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Documents\DeliveryNoteController;
use App\Http\Controllers\Documents\DeliveryReturnController;
use App\Http\Controllers\Documents\InvoiceController;
use App\Http\Controllers\Documents\QuoteController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Products\CategoryController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Settings\TaxController;
use App\Http\Controllers\Public\PublicDeliveryController;
use App\Http\Controllers\Inventory\InventoryDashboardController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\InventoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CommercialiZe Suite
|--------------------------------------------------------------------------
*/

// ============================================
// ROUTES PUBLIQUES (non authentifiées)
// ============================================

// Bon de livraison public (QR code)
Route::get('/bl/{token}', [PublicDeliveryController::class, 'show'])->name('delivery.public');
Route::post('/bl/{token}/verify', [PublicDeliveryController::class, 'verify'])
    ->middleware('throttle:5,60')
    ->name('delivery.public.verify');

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
})->name('home');

// ============================================
// AUTHENTIFICATION
// ============================================

Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // Registration
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // Password Reset
    Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');
});

// Logout (authentifié)
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================
// ROUTES AUTHENTIFIÉES
// ============================================

Route::middleware(['auth'])->group(function () {

    // ----------------------------------------
    // DASHBOARD
    // ----------------------------------------
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----------------------------------------
    // PROFIL UTILISATEUR
    // ----------------------------------------
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::put('password/change', [PasswordController::class, 'change']);

    // ----------------------------------------
    // ADMINISTRATION (Entreprises, Sites, Users, Roles)
    // ----------------------------------------
    Route::prefix('admin')->name('admin.')->group(function () {

        // Entreprises (super admin)
        Route::resource('companies', CompanyController::class);
        Route::post('companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])
            ->name('companies.toggle-status');

        // Sites
        Route::resource('sites', SiteController::class);
        Route::post('sites/{site}/toggle-status', [SiteController::class, 'toggleStatus'])
            ->name('sites.toggle-status');
        Route::get('sites/{site}/users', [SiteController::class, 'users'])->name('sites.users');
        Route::post('sites/{site}/users', [SiteController::class, 'addUser'])->name('sites.users.add');
        Route::delete('sites/{site}/users/{user}', [SiteController::class, 'removeUser'])
            ->name('sites.users.remove');

        // Utilisateurs
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        // Rôles
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });

    // Promotions (accessible à tous les utilisateurs authentifiés avec permissions)
    Route::get('promotions/apply', [PromotionController::class, 'apply'])->name('promotions.apply');
    Route::resource('promotions', PromotionController::class)->except(['show']);

    // Paramètres entreprise (pour l'utilisateur courant)
    Route::get('settings/company', [CompanyController::class, 'settings'])->name('settings.company');
    Route::put('settings/company', [CompanyController::class, 'updateSettings'])->name('settings.company.update');

    // ----------------------------------------
    // PRODUITS & CATÉGORIES
    // ----------------------------------------
    
    // Catégories
    Route::resource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    // Produits
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');
    Route::post('products/{product}/toggle-online', [ProductController::class, 'toggleOnline'])
        ->name('products.toggle-online');
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])
        ->name('products.duplicate');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])
        ->name('products.images.delete');
    Route::post('products/{product}/images/{image}/primary', [ProductController::class, 'setImagePrimary'])
        ->name('products.images.primary');

    // ----------------------------------------
    // CLIENTS
    // ----------------------------------------
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])
        ->name('clients.toggle-status');
    Route::get('clients-export', [ClientController::class, 'export'])->name('clients.export');
    Route::get('clients-import', [ClientController::class, 'importForm'])->name('clients.import.form');
    Route::post('clients-import', [ClientController::class, 'import'])->name('clients.import');
    Route::get('clients-search', [ClientController::class, 'search'])->name('clients.search');

    // ----------------------------------------
    // DEVIS
    // ----------------------------------------
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::post('quotes/{quote}/decline', [QuoteController::class, 'decline'])->name('quotes.decline');
    Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.updateStatus');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');

    // ----------------------------------------
    // FACTURES
    // ----------------------------------------
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');
    Route::post('invoices/{invoice}/generate-pin', [InvoiceController::class, 'generatePin'])->name('invoices.generatePin');

    // ----------------------------------------
    // BONS DE LIVRAISON
    // ----------------------------------------
    Route::resource('deliveries', DeliveryNoteController::class);
    Route::get('deliveries/{delivery}/pdf', [DeliveryNoteController::class, 'pdf'])->name('deliveries.pdf');
    Route::patch('deliveries/{delivery}/status', [DeliveryNoteController::class, 'updateStatus'])->name('deliveries.updateStatus');
    Route::post('deliveries/{delivery}/signature', [DeliveryNoteController::class, 'saveSignature'])->name('deliveries.signature');
    Route::post('deliveries/{delivery}/verify-pin', [DeliveryNoteController::class, 'verifyPin'])->name('deliveries.verifyPin');
    Route::post('invoices/{invoice}/delivery', [DeliveryNoteController::class, 'createFromInvoice'])->name('deliveries.createFromInvoice');

    // ----------------------------------------
    // RETOURS CLIENTS
    // ----------------------------------------
    Route::resource('returns', DeliveryReturnController::class)->except(['edit', 'update']);
    Route::patch('returns/{return}/receive', [DeliveryReturnController::class, 'markReceived'])->name('returns.receive');
    Route::post('returns/{return}/resolve',  [DeliveryReturnController::class, 'resolve'])->name('returns.resolve');

    // ----------------------------------------
    // PAIEMENTS
    // ----------------------------------------
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // ----------------------------------------
    // TAXES (Paramètres)
    // ----------------------------------------
    Route::resource('taxes', TaxController::class)->except(['show']);
    Route::post('taxes/{tax}/toggle-status', [TaxController::class, 'toggleStatus'])
        ->name('taxes.toggle-status');
    Route::post('taxes/{tax}/set-default', [TaxController::class, 'setDefault'])
        ->name('taxes.set-default');

    // ----------------------------------------
    // ROUTES AVEC FEATURE FLAGS
    // ----------------------------------------

    // E-commerce (version Pro+)
    Route::middleware(['feature:ecommerce'])->prefix('ecommerce')->name('ecommerce.')->group(function () {
        Route::get('/', fn () => view('coming-soon', ['feature' => 'E-commerce']))->name('store');
        Route::get('/orders', fn () => view('coming-soon', ['feature' => 'Commandes']))->name('orders');
    });

    // Gestion des stocks
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', InventoryDashboardController::class)->name('index');

        // Entrepôts
        Route::resource('warehouses', WarehouseController::class);

        // Mouvements de stock
        Route::get('movements', [StockMovementController::class, 'index'])->name('movements.index');
        Route::get('movements/create', [StockMovementController::class, 'create'])->name('movements.create');
        Route::post('movements', [StockMovementController::class, 'store'])->name('movements.store');
        Route::get('movements/{movement}', [StockMovementController::class, 'show'])->name('movements.show');

        // Sessions d'inventaire
        Route::get('sessions', [InventoryController::class, 'index'])->name('sessions.index');
        Route::get('sessions/create', [InventoryController::class, 'create'])->name('sessions.create');
        Route::post('sessions', [InventoryController::class, 'store'])->name('sessions.store');
        Route::get('sessions/{session}', [InventoryController::class, 'show'])->name('sessions.show');
        Route::patch('sessions/{session}/lines/{line}', [InventoryController::class, 'updateLine'])->name('sessions.line');
        Route::post('sessions/{session}/complete', [InventoryController::class, 'complete'])->name('sessions.complete');
    });

    // Rapports avancés (version Pro+)
    Route::middleware(['feature:reports_advanced'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', fn () => view('coming-soon', ['feature' => 'Rapports avancés']))->name('index');
    });

    // ----------------------------------------
    // RECHERCHE GLOBALE
    // ----------------------------------------
    Route::get('search', fn () => view('coming-soon', ['feature' => 'Recherche globale']))->name('search');

    // ----------------------------------------
    // SWITCH SITE (Enterprise)
    // ----------------------------------------
    Route::post('switch-site/{site}', function ($siteId) {
        auth()->user()->update(['current_site_id' => $siteId]);
        return redirect()->back()->with('success', 'Site changé.');
    })->name('switch-site');

    // ----------------------------------------
    // JOURNAL D'ACTIVITÉ
    // ----------------------------------------
    Route::get('activity', fn () => view('coming-soon', ['feature' => "Journal d'activité"]))->name('activity.index');

    // ----------------------------------------
    // NOTIFICATIONS
    // ----------------------------------------
    Route::get('notifications', function () {
        $notifications = auth()->user()->notifications()->paginate(20);
        auth()->user()->unreadNotifications->markAsRead();
        return view('coming-soon', ['feature' => 'Notifications']);
    })->name('notifications.index');

    Route::post('notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'Notifications marquées comme lues.');
    })->name('notifications.mark-all-read');

    // ----------------------------------------
    // API INTERNE (AJAX)
    // ----------------------------------------
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');
        Route::get('products/search', function () {
            // À implémenter
        })->name('products.search');
    });

    // ----------------------------------------
    // PAGES D'UPGRADE
    // ----------------------------------------
    Route::view('subscription/plans', 'subscription.plans')->name('subscription.plans');
    Route::view('pricing', 'subscription.plans')->name('pricing.plans');
});

// ============================================
// FALLBACK 404
// ============================================
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
