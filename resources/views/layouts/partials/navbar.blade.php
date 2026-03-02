{{-- Top Navbar --}}
<header class="admin-navbar">
    <div class="d-flex align-items-center justify-content-between">
        {{-- Left side --}}
        <div class="d-flex align-items-center">
            {{-- Mobile toggle --}}
            <button type="button" class="btn btn-link text-dark d-lg-none me-2" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            
            {{-- Search --}}
            <form action="{{ route('search') }}" method="GET" class="navbar-search d-none d-md-block">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="q" placeholder="Rechercher..." value="{{ request('q') }}">
                </div>
            </form>
        </div>
        
        {{-- Right side --}}
        <div class="navbar-menu d-flex align-items-center">
            {{-- Quick Actions --}}
            <div class="dropdown me-3">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-1"></i>
                    <span class="d-none d-md-inline">Créer</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @can('quotes.create')
                    <li>
                        <a class="dropdown-item" href="{{ route('quotes.create') }}">
                            <i class="fas fa-file-alt me-2 text-muted"></i>Nouveau devis
                        </a>
                    </li>
                    @endcan
                    @can('invoices.create')
                    <li>
                        <a class="dropdown-item" href="{{ route('invoices.create') }}">
                            <i class="fas fa-file-invoice me-2 text-muted"></i>Nouvelle facture
                        </a>
                    </li>
                    @endcan
                    @can('clients.create')
                    <li>
                        <a class="dropdown-item" href="{{ route('clients.create') }}">
                            <i class="fas fa-user-plus me-2 text-muted"></i>Nouveau client
                        </a>
                    </li>
                    @endcan
                    @can('products.create')
                    <li>
                        <a class="dropdown-item" href="{{ route('products.create') }}">
                            <i class="fas fa-box me-2 text-muted"></i>Nouveau produit
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            
            {{-- Site Selector (Enterprise) --}}
            @feature('multi_sites')
            @if(auth()->user()->sites->count() > 1)
            <div class="dropdown me-3">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    {{ auth()->user()->currentSite?->name ?? 'Site' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(auth()->user()->sites as $site)
                    <li>
                        <a class="dropdown-item {{ auth()->user()->current_site_id == $site->id ? 'active' : '' }}" 
                           href="{{ route('switch-site', $site->id) }}">
                            <i class="fas fa-{{ $site->is_headquarters ? 'star' : 'building' }} me-2"></i>
                            {{ $site->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @endfeature
            
            {{-- Notifications --}}
            <div class="dropdown me-3">
                <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 320px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Notifications</span>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                        <a href="{{ route('notifications.mark-all-read') }}" class="small text-primary">Tout marquer lu</a>
                        @endif
                    </div>
                    <div class="dropdown-divider"></div>
                    
                    @forelse(auth()->user()->notifications->take(5) as $notification)
                    <a class="dropdown-item py-2 {{ $notification->read_at ? '' : 'bg-light' }}" href="{{ $notification->data['url'] ?? '#' }}">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-{{ $notification->data['icon'] ?? 'info-circle' }} text-{{ $notification->data['type'] ?? 'primary' }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="small fw-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                <div class="small text-muted">{{ $notification->data['message'] ?? '' }}</div>
                                <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="dropdown-item text-center text-muted py-3">
                        <i class="fas fa-bell-slash me-1"></i> Aucune notification
                    </div>
                    @endforelse
                    
                    @if(auth()->user()->notifications->count() > 5)
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center small" href="{{ route('notifications.index') }}">
                        Voir toutes les notifications
                    </a>
                    @endif
                </div>
            </div>
            
            {{-- User Menu --}}
            <div class="dropdown">
                <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <div class="avatar avatar-sm me-2 bg-primary">
                        @if(auth()->user()->avatar_path)
                            <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="Avatar">
                        @else
                            <span class="avatar-text">{{ substr(auth()->user()->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="small text-muted">{{ auth()->user()->email }}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="fas fa-user me-2 text-muted"></i>Mon profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('password.change') }}">
                            <i class="fas fa-lock me-2 text-muted"></i>Mot de passe
                        </a>
                    </li>
                    @can('settings.view')
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.company') }}">
                            <i class="fas fa-cog me-2 text-muted"></i>Paramètres
                        </a>
                    </li>
                    @endcan
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
