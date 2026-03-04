@extends('layouts.admin')

@section('title', $user->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
<li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">{{ $user->name }}</h1>
        <p class="text-muted mb-0">{{ $user->job_title ?? $user->email }}</p>
    </div>
    <div class="d-flex gap-2">
        @can('users.edit')
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        @endcan
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        {{-- Profil --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                @if($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" alt=""
                         class="rounded-circle mb-3"
                         style="width: 80px; height: 80px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px;">
                        <span class="text-primary fw-bold fs-4">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    </div>
                @endif
                <h5 class="mb-1">{{ $user->name }}</h5>
                @if($user->job_title)
                    <p class="text-muted small mb-2">{{ $user->job_title }}</p>
                @endif
                @foreach($user->roles as $role)
                    <span class="badge" style="background-color: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
                @endforeach
                <div class="mt-2">
                    @if($user->is_active)
                        <span class="badge bg-success">Actif</span>
                    @else
                        <span class="badge bg-secondary">Inactif</span>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $user->email }}</li>
                    @if($user->phone)
                    <li class="mb-1"><i class="fas fa-phone me-2 text-muted"></i>{{ $user->phone }}</li>
                    @endif
                    <li><i class="fas fa-clock me-2 text-muted"></i>
                        @if($user->last_login_at)
                            Dernière connexion {{ $user->last_login_at->diffForHumans() }}
                        @else
                            Jamais connecté
                        @endif
                    </li>
                </ul>
            </div>
        </div>

        {{-- Sites --}}
        @if($user->sites->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Sites accessibles</h6>
            </div>
            <div class="card-body">
                @foreach($user->sites as $site)
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-store me-2 text-muted"></i>
                    <span>{{ $site->name }}</span>
                    @if($site->pivot->is_primary)
                        <span class="badge bg-warning text-dark ms-auto">Principal</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} w-100">
                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} me-2"></i>
                        {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>
                @can('users.delete')
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Supprimer cet utilisateur définitivement ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
                @endcan
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Statistiques --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-primary">{{ $stats['quotes_count'] }}</div>
                        <div class="text-muted small">Devis créés</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-success">{{ $stats['invoices_count'] }}</div>
                        <div class="text-muted small">Factures créées</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-warning">{{ number_format($stats['total_sales'], 0, ',', ' ') }}</div>
                        <div class="text-muted small">CA total (FCFA)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informations --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations du compte</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Nom</label>
                        <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Email</label>
                        <p class="mb-0 fw-semibold">{{ $user->email }}</p>
                    </div>
                    @if($user->phone)
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Téléphone</label>
                        <p class="mb-0 fw-semibold">{{ $user->phone }}</p>
                    </div>
                    @endif
                    @if($user->job_title)
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Fonction</label>
                        <p class="mb-0 fw-semibold">{{ $user->job_title }}</p>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Compte créé le</label>
                        <p class="mb-0 fw-semibold">{{ $user->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-0">Dernière mise à jour</label>
                        <p class="mb-0 fw-semibold">{{ $user->updated_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
