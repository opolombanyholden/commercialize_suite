{{-- Sidebar Navigation --}}
<aside class="admin-sidebar" id="sidebar">
    {{-- Header --}}
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            @if(auth()->user()->company?->logo_path)
                <img src="{{ Storage::url(auth()->user()->company->logo_path) }}" alt="Logo" class="sidebar-logo">
            @else
                <i class="fas fa-chart-line me-2"></i>
                <span>CommercialiZe</span>
            @endif
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none" id="sidebarClose"></button>
    </div>
    
    {{-- User Info --}}
    <div class="sidebar-user">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm me-3 bg-primary">
                @if(auth()->user()->avatar_path)
                    <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="Avatar">
                @else
                    <span class="avatar-text">{{ substr(auth()->user()->name, 0, 2) }}</span>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold text-white">{{ auth()->user()->name }}</div>
                <small class="text-white-50">{{ auth()->user()->roles->first()?->name ?? 'Utilisateur' }}</small>
            </div>
        </div>
    </div>
    
    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            {{-- Dashboard --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            
            {{-- Documents Section --}}
            <li class="nav-header">Documents</li>
            
            @can('quotes.view')
            <li class="nav-item">
                <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt nav-icon"></i>
                    <span>Devis</span>
                </a>
            </li>
            @endcan
            
            @can('invoices.view')
            <li class="nav-item">
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice nav-icon"></i>
                    <span>Factures</span>
                </a>
            </li>
            @endcan
            
            @can('payments.view')
            <li class="nav-item">
                <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave nav-icon"></i>
                    <span>Paiements</span>
                </a>
            </li>
            @endcan

            @can('deliveries.view')
            <li class="nav-item">
                <a href="{{ route('deliveries.index') }}" class="nav-link {{ request()->routeIs('deliveries.*') ? 'active' : '' }}">
                    <i class="fas fa-truck nav-icon"></i>
                    <span>Bons de livraison</span>
                </a>
            </li>
            @endcan

            @can('returns.view')
            <li class="nav-item">
                <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                    <i class="fas fa-undo nav-icon"></i>
                    <span>Retours clients</span>
                </a>
            </li>
            @endcan

            {{-- Catalog Section --}}
            @canany(['products.view', 'categories.view', 'clients.view'])
            <li class="nav-header">Catalogue</li>
            @endcanany
            
            @can('products.view')
            <li class="nav-item">
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box nav-icon"></i>
                    <span>Produits</span>
                </a>
            </li>
            @endcan
            
            @can('categories.view')
            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags nav-icon"></i>
                    <span>Catégories</span>
                </a>
            </li>
            @endcan
            
            @can('clients.view')
            <li class="nav-item">
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="fas fa-users nav-icon"></i>
                    <span>Clients</span>
                </a>
            </li>
            @endcan
            
            {{-- Inventory Section (Pro+) --}}
            @feature('inventory')
            @can('inventory.view')
            <li class="nav-header">Stock</li>
            <li class="nav-item">
                <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse nav-icon"></i>
                    <span>Inventaire</span>
                </a>
            </li>
            @endcan
            @endfeature
            
            {{-- E-commerce Section (Pro+) --}}
            @feature('ecommerce')
            @can('ecommerce.manage_store')
            <li class="nav-header">E-commerce</li>
            <li class="nav-item">
                <a href="{{ route('ecommerce.store') }}" class="nav-link {{ request()->routeIs('ecommerce.store*') ? 'active' : '' }}">
                    <i class="fas fa-store nav-icon"></i>
                    <span>Ma boutique</span>
                </a>
            </li>
            @endcan
            
            @can('ecommerce.view_orders')
            <li class="nav-item">
                <a href="{{ route('ecommerce.orders') }}" class="nav-link {{ request()->routeIs('ecommerce.orders*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    <span>Commandes</span>
                </a>
            </li>
            @endcan
            @endfeature
            
            {{-- Reports Section --}}
            @can('reports.view')
            <li class="nav-header">Rapports</li>
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    <span>Rapports</span>
                </a>
            </li>
            @endcan
            
            {{-- Administration Section --}}
            @canany(['sites.view', 'users.view', 'roles.view', 'taxes.view', 'settings.view', 'promotions.view'])
            <li class="nav-header">Administration</li>
            @endcanany

            @can('sites.view')
            <li class="nav-item">
                <a href="{{ route('admin.sites.index') }}" class="nav-link {{ request()->routeIs('admin.sites.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt nav-icon"></i>
                    <span>Sites</span>
                </a>
            </li>
            @endcan

            @can('promotions.view')
            <li class="nav-item">
                <a href="{{ route('promotions.index') }}" class="nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}">
                    <i class="fas fa-tag nav-icon"></i>
                    <span>Promotions</span>
                </a>
            </li>
            @endcan

            @can('users.view')
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog nav-icon"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            @endcan
            
            @can('roles.view')
            <li class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt nav-icon"></i>
                    <span>Rôles</span>
                </a>
            </li>
            @endcan
            
            @can('taxes.view')
            <li class="nav-item">
                <a href="{{ route('taxes.index') }}" class="nav-link {{ request()->routeIs('taxes.*') ? 'active' : '' }}">
                    <i class="fas fa-percent nav-icon"></i>
                    <span>Taxes</span>
                </a>
            </li>
            @endcan
            
            @can('settings.view')
            <li class="nav-item">
                <a href="{{ route('settings.company') }}" class="nav-link {{ request()->routeIs('settings.company*') ? 'active' : '' }}">
                    <i class="fas fa-building nav-icon"></i>
                    <span>Entreprise</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.documents.index') }}" class="nav-link {{ request()->routeIs('settings.documents.*') ? 'active' : '' }}">
                    <i class="fas fa-palette nav-icon"></i>
                    <span>Style documents</span>
                </a>
            </li>
            @endcan
        </ul>
    </nav>
    
    {{-- Footer --}}
    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="badge bg-{{ auth()->user()->version === 'enterprise' ? 'danger' : (auth()->user()->version === 'pro' ? 'warning' : (auth()->user()->version === 'standard' ? 'info' : 'secondary')) }}">
                {{ strtoupper(auth()->user()->version ?? 'LIGHT') }}
            </span>
            <a href="{{ route('profile') }}" class="text-white-50" title="Mon profil">
                <i class="fas fa-cog"></i>
            </a>
        </div>
    </div>
</aside>
