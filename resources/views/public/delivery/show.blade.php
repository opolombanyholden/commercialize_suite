@extends('layouts.public')

@section('title', 'Bon de livraison ' . $delivery->delivery_number)

@section('content')

{{-- En-tête --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-4">
        @if($delivery->company->logo_path)
            <img src="{{ asset('storage/' . $delivery->company->logo_path) }}" alt="{{ $delivery->company->name }}" height="60" class="mb-3">
        @endif
        <h4 class="mb-1">{{ $delivery->company->name }}</h4>
        <p class="text-muted mb-0">Bon de livraison N° <strong>{{ $delivery->delivery_number }}</strong></p>
    </div>
</div>

{{-- Alertes flash --}}
@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
@endif
@if(session('info'))
    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>{{ session('info') }}</div>
@endif

{{-- Statut livraison confirmée --}}
@if($delivery->isPinVerified())
<div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
    <div class="card-body text-center py-4">
        <div class="mb-3">
            <span class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                <i class="fas fa-check-circle text-success fs-2"></i>
            </span>
        </div>
        <h5 class="text-success mb-1">Livraison confirmée</h5>
        <p class="text-muted mb-0 small">
            Confirmée par {{ $delivery->pin_verified_by === 'client' ? 'le client' : 'le livreur' }}
            le {{ $delivery->pin_verified_at->format('d/m/Y à H:i') }}
        </p>
    </div>
</div>
@endif

{{-- Informations BL --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i>Détails de la livraison</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $delivery->client_name }}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Date prévue</div>
                <div class="fw-semibold">{{ $delivery->planned_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            @if($delivery->delivery_address)
            <div class="col-12">
                <div class="small text-muted">Adresse de livraison</div>
                <div>{{ $delivery->delivery_address }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Articles --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Articles</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-center" style="width:100px;">Quantité</th>
                        <th style="width:60px;">Unité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivery->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ number_format((float)$item->quantity, 2, ',', ' ') }}</td>
                        <td class="text-muted small">{{ $item->unit ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Formulaire PIN (uniquement si non encore vérifié et facture a un PIN) --}}
@if(!$delivery->isPinVerified() && $delivery->invoice?->hasDeliveryPin())
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="fas fa-key me-2 text-primary"></i>Confirmer la réception</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Saisissez le code de livraison indiqué sur votre facture pour confirmer la réception de cette commande.
        </p>
        <form action="{{ route('delivery.public.verify', $delivery->public_token) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Code de livraison</label>
                <input type="text" name="pin"
                    class="form-control pin-input @error('pin') is-invalid @enderror"
                    placeholder="XXXXXXXX"
                    maxlength="8"
                    autocomplete="off"
                    required>
                @error('pin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check me-2"></i>Confirmer la réception
                </button>
            </div>
        </form>
    </div>
</div>
@elseif(!$delivery->isPinVerified())
<div class="alert alert-secondary text-center">
    <i class="fas fa-info-circle me-2"></i>La confirmation par PIN n'est pas disponible pour ce bon de livraison.
</div>
@endif

@endsection
