@extends('layouts.admin')

@section('title', 'Mon profil')

@section('breadcrumb')
<li class="breadcrumb-item active">Mon profil</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        {{-- Profile Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if(auth()->user()->avatar_path)
                        <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="avatar-xl mx-auto bg-primary bg-opacity-10 text-primary">
                            <span class="fs-1 fw-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </div>
                    @endif
                </div>
                <h4 class="mb-1">{{ auth()->user()->name }}</h4>
                <p class="text-muted mb-2">{{ auth()->user()->job_title ?? 'Utilisateur' }}</p>
                @foreach(auth()->user()->roles as $role)
                    <span class="badge" style="background-color: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
                @endforeach
                
                <hr class="my-4">
                
                <div class="text-start">
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <span><i class="fas fa-envelope me-2 text-muted"></i>{{ auth()->user()->email }}</span>
                    </div>
                    @if(auth()->user()->phone)
                    <div class="mb-3">
                        <small class="text-muted d-block">Téléphone</small>
                        <span><i class="fas fa-phone me-2 text-muted"></i>{{ auth()->user()->phone }}</span>
                    </div>
                    @endif
                    <div class="mb-3">
                        <small class="text-muted d-block">Entreprise</small>
                        <span><i class="fas fa-building me-2 text-muted"></i>{{ auth()->user()->company->name ?? '-' }}</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Membre depuis</small>
                        <span><i class="fas fa-calendar me-2 text-muted"></i>{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100">
                    <i class="fas fa-edit me-2"></i>Modifier mon profil
                </a>
            </div>
        </div>
        
        {{-- Sites Access --}}
        @if(auth()->user()->sites->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Mes sites</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach(auth()->user()->sites as $site)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            {{ $site->name }}
                            @if($site->is_headquarters)
                                <i class="fas fa-star text-warning ms-1" title="Siège"></i>
                            @endif
                        </span>
                        <span class="badge bg-light text-muted">{{ $site->city ?? '-' }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-8">
        {{-- Activity Stats --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-0">{{ $stats['invoices_count'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Factures créées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-0">{{ $stats['quotes_count'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Devis créés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-0">{{ $stats['clients_count'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Clients gérés</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Recent Activity --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-history me-2 text-primary"></i>Activité récente</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($activities ?? [] as $activity)
                    <li class="list-group-item">
                        <div class="d-flex">
                            <div class="activity-icon me-3 bg-{{ $activity->color ?? 'primary' }} bg-opacity-10 text-{{ $activity->color ?? 'primary' }}">
                                <i class="fas fa-{{ $activity->icon ?? 'circle' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0">{{ $activity->description }}</p>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">
                        Aucune activité récente
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
        
        {{-- Security --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Sécurité</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="mb-0 fw-semibold">Mot de passe</p>
                        <small class="text-muted">Dernière modification : {{ auth()->user()->password_changed_at?->diffForHumans() ?? 'Jamais' }}</small>
                    </div>
                    <a href="{{ route('profile.edit') }}#password" class="btn btn-sm btn-outline-primary">Modifier</a>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 fw-semibold">Dernière connexion</p>
                        <small class="text-muted">
                            @if(auth()->user()->last_login_at)
                                {{ auth()->user()->last_login_at->format('d/m/Y à H:i') }}
                                @if(auth()->user()->last_login_ip)
                                    depuis {{ auth()->user()->last_login_ip }}
                                @endif
                            @else
                                Première connexion
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-xl {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>
@endpush
