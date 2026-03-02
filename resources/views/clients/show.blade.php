@extends('layouts.admin')

@section('title', $client->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
<li class="breadcrumb-item active">{{ Str::limit($client->name, 35) }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Client Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-{{ $client->type === 'business' ? 'primary' : 'info' }} bg-opacity-10">
                            <i class="fas fa-{{ $client->type === 'business' ? 'building' : 'user' }} fa-2x text-{{ $client->type === 'business' ? 'primary' : 'info' }}"></i>
                        </div>
                        <div>
                            <h3 class="mb-1">{{ $client->name }}</h3>
                            @if($client->company_name && $client->type === 'individual')
                                <p class="text-muted mb-1">{{ $client->company_name }}</p>
                            @endif
                            <span class="badge bg-{{ $client->type === 'business' ? 'primary' : 'info' }}">
                                {{ $client->type === 'business' ? 'Entreprise' : 'Particulier' }}
                            </span>
                        </div>
                    </div>
                    @can('clients.edit')
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Modifier
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        {{-- Stats Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0 text-primary">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }}</h2>
                        <p class="text-muted mb-0">FCFA - Chiffre d'affaires</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0 text-success">{{ $stats['invoices_count'] ?? 0 }}</h2>
                        <p class="text-muted mb-0">Factures émises</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0 text-{{ ($stats['unpaid_amount'] ?? 0) > 0 ? 'warning' : 'success' }}">
                            {{ number_format($stats['unpaid_amount'] ?? 0, 0, ',', ' ') }}
                        </h2>
                        <p class="text-muted mb-0">FCFA - En attente</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Invoices History --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2 text-primary"></i>Historique des factures
                </h5>
                @can('invoices.create')
                <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Nouvelle facture
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Facture</th>
                                <th>Date</th>
                                <th class="text-end">Montant</th>
                                <th class="text-center">Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices ?? [] as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->total_amount, 0, ',', ' ') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $invoice->status_color }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Aucune facture pour ce client
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Contact Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">
                    <i class="fas fa-address-book me-2 text-primary"></i>Contact
                </h5>
            </div>
            <div class="card-body">
                @if($client->email)
                <div class="mb-3">
                    <small class="text-muted d-block">Email</small>
                    <a href="mailto:{{ $client->email }}" class="text-decoration-none">
                        <i class="fas fa-envelope me-1 text-muted"></i>{{ $client->email }}
                    </a>
                </div>
                @endif
                
                @if($client->phone)
                <div class="mb-3">
                    <small class="text-muted d-block">Téléphone</small>
                    <a href="tel:{{ $client->phone }}" class="text-decoration-none">
                        <i class="fas fa-phone me-1 text-muted"></i>{{ $client->phone }}
                    </a>
                </div>
                @endif
                
                @if($client->tax_id)
                <div class="mb-0">
                    <small class="text-muted d-block">N° Contribuable</small>
                    <span><i class="fas fa-id-card me-1 text-muted"></i>{{ $client->tax_id }}</span>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Address --}}
        @if($client->address || $client->city)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Adresse
                </h5>
            </div>
            <div class="card-body">
                @if($client->address)
                    <p class="mb-1">{{ $client->address }}</p>
                @endif
                @if($client->city || $client->postal_code)
                    <p class="mb-1">{{ $client->postal_code }} {{ $client->city }}</p>
                @endif
                @if($client->country)
                    <p class="mb-0">{{ $client->country }}</p>
                @endif
            </div>
        </div>
        @endif
        
        {{-- Notes --}}
        @if($client->notes)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sticky-note me-2 text-primary"></i>Notes
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0 text-muted">{!! nl2br(e($client->notes)) !!}</p>
            </div>
        </div>
        @endif
        
        {{-- Actions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('invoices.create')
                    <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="btn btn-primary">
                        <i class="fas fa-file-invoice me-2"></i>Nouvelle facture
                    </a>
                    @endcan
                    
                    @can('quotes.create')
                    <a href="{{ route('quotes.create', ['client_id' => $client->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-alt me-2"></i>Nouveau devis
                    </a>
                    @endcan
                    
                    @can('clients.delete')
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-lg {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
