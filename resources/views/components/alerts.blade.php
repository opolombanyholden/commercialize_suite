{{-- Flash Messages & Validation Errors Component --}}

{{-- Success Alert --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="fas fa-check-circle me-2 fa-lg"></i>
    <div class="flex-grow-1">
        {{ session('success') }}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Error Alert --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
    <div class="flex-grow-1">
        {{ session('error') }}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Warning Alert --}}
@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
    <div class="flex-grow-1">
        {{ session('warning') }}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Info Alert --}}
@if(session('info'))
<div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="fas fa-info-circle me-2 fa-lg"></i>
    <div class="flex-grow-1">
        {{ session('info') }}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Status Alert (generic) --}}
@if(session('status'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="fas fa-check-circle me-2 fa-lg"></i>
    <div class="flex-grow-1">
        {{ session('status') }}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Message Alert (generic) --}}
@if(session('message'))
<div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
    <i class="fas fa-info-circle me-2 fa-lg"></i>
    <div class="flex-grow-1">
        {{ session('message') }}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-start">
        <i class="fas fa-times-circle me-2 fa-lg mt-1"></i>
        <div class="flex-grow-1">
            <strong>Oups ! Veuillez corriger les erreurs suivantes :</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Feature Upgrade Notice --}}
@if(session('upgrade_required'))
<div class="alert alert-primary alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-crown me-3 fa-lg text-warning"></i>
        <div class="flex-grow-1">
            <strong>Fonctionnalité Premium</strong>
            <p class="mb-2">{{ session('upgrade_required') }}</p>
            <a href="{{ route('pricing') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-up me-1"></i>Mettre à niveau
            </a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Custom Alert with Title --}}
@if(session('alert'))
@php $alert = session('alert'); @endphp
<div class="alert alert-{{ $alert['type'] ?? 'info' }} alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-start">
        <i class="fas fa-{{ $alert['icon'] ?? 'info-circle' }} me-2 fa-lg mt-1"></i>
        <div class="flex-grow-1">
            @if(isset($alert['title']))
                <strong>{{ $alert['title'] }}</strong><br>
            @endif
            {{ $alert['message'] }}
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Invoice/Document Created Success --}}
@if(session('document_created'))
@php $doc = session('document_created'); @endphp
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-file-check me-3 fa-2x text-success"></i>
        <div class="flex-grow-1">
            <strong>{{ $doc['type'] ?? 'Document' }} créé avec succès !</strong>
            <p class="mb-2">Numéro : <strong>{{ $doc['number'] ?? '' }}</strong></p>
            <div class="btn-group btn-group-sm">
                @if(isset($doc['pdf_url']))
                <a href="{{ $doc['pdf_url'] }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>Télécharger PDF
                </a>
                @endif
                @if(isset($doc['show_url']))
                <a href="{{ $doc['show_url'] }}" class="btn btn-outline-success">
                    <i class="fas fa-eye me-1"></i>Voir
                </a>
                @endif
            </div>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

{{-- Payment Recorded Success --}}
@if(session('payment_recorded'))
@php $payment = session('payment_recorded'); @endphp
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-money-check-alt me-3 fa-2x text-success"></i>
        <div class="flex-grow-1">
            <strong>Paiement enregistré !</strong>
            <p class="mb-0">
                Montant : <strong>{{ number_format($payment['amount'] ?? 0, 0, ',', ' ') }} FCFA</strong>
                @if(isset($payment['balance']) && $payment['balance'] > 0)
                    <br><span class="text-muted">Reste à payer : {{ number_format($payment['balance'], 0, ',', ' ') }} FCFA</span>
                @else
                    <br><span class="text-success"><i class="fas fa-check me-1"></i>Facture entièrement payée</span>
                @endif
            </p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif
