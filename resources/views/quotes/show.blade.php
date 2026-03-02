@extends('layouts.admin')

@section('title', 'Devis ' . $quote->quote_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Devis</a></li>
<li class="breadcrumb-item active">{{ $quote->quote_number }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Quote Header --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ $quote->quote_number }}</h3>
                        <p class="text-muted mb-0">
                            Créé le {{ $quote->quote_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $quote->status_color }} fs-6 mb-2">
                            {{ $quote->status_label }}
                        </span>
                        @if($quote->converted_to_invoice_id)
                            <br>
                            <a href="{{ route('invoices.show', $quote->converted_to_invoice_id) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-invoice me-1"></i>Voir la facture
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Client & Quote Info --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Client</h6>
                    </div>
                    <div class="card-body">
                        <p class="fw-semibold mb-1">{{ $quote->client_name }}</p>
                        @if($quote->client_email)
                            <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $quote->client_email }}</p>
                        @endif
                        @if($quote->client_phone)
                            <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i>{{ $quote->client_phone }}</p>
                        @endif
                        @if($quote->client_address)
                            <p class="mb-0 text-muted">{{ $quote->client_address }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-calendar me-2 text-primary"></i>Validité</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Date du devis</span>
                            <span class="fw-semibold">{{ $quote->quote_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Valide jusqu'au</span>
                            <span class="fw-semibold {{ $quote->is_expired ? 'text-danger' : '' }}">
                                {{ $quote->valid_until->format('d/m/Y') }}
                            </span>
                        </div>
                        @if($quote->is_expired && !$quote->converted_to_invoice_id)
                            <div class="alert alert-warning mb-0 py-2">
                                <small><i class="fas fa-clock me-1"></i>Ce devis a expiré</small>
                            </div>
                        @elseif(!$quote->is_expired && !$quote->converted_to_invoice_id)
                            <div class="alert alert-info mb-0 py-2">
                                <small><i class="fas fa-hourglass-half me-1"></i>Expire dans {{ $quote->valid_until->diffInDays(now()) }} jour(s)</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Items Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Articles</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">P.U.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->type === 'service' ? 'info' : 'warning' }}">
                                        {{ $item->type === 'service' ? 'Service' : 'Produit' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item->total, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end">Sous-total HT</td>
                                <td class="text-end fw-semibold">{{ number_format($quote->subtotal, 0, ',', ' ') }}</td>
                            </tr>
                            @foreach($quote->taxes as $tax)
                            <tr>
                                <td colspan="5" class="text-end">{{ $tax->tax_name }} ({{ $tax->tax_rate }}%)</td>
                                <td class="text-end">{{ number_format($tax->tax_amount, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold fs-5">
                                <td colspan="5" class="text-end">Total TTC</td>
                                <td class="text-end text-primary">{{ number_format($quote->total_amount, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Amount Summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center">
                <p class="text-muted mb-1">Montant total</p>
                <h2 class="mb-1 text-primary">{{ number_format($quote->total_amount, 0, ',', ' ') }}</h2>
                <p class="text-muted mb-0">FCFA</p>
            </div>
        </div>
        
        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                    </a>
                    
                    @if($quote->client_email)
                    <button type="button" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Envoyer par email
                    </button>
                    @endif
                    
                    @if(!$quote->converted_to_invoice_id && $quote->status !== 'rejected')
                        @can('quotes.convert')
                        <a href="{{ route('quotes.convert', $quote) }}" class="btn btn-success" onclick="return confirm('Convertir ce devis en facture ?')">
                            <i class="fas fa-file-invoice me-2"></i>Convertir en facture
                        </a>
                        @endcan
                        
                        @can('quotes.edit')
                        <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        @endcan
                    @endif
                    
                    <a href="{{ route('quotes.duplicate', $quote) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-copy me-2"></i>Dupliquer
                    </a>
                    
                    @can('quotes.delete')
                    <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('Supprimer ce devis ?')">
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
        
        {{-- Status Update --}}
        @if(!$quote->converted_to_invoice_id)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Changer le statut</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    Statut actuel : <span class="badge bg-{{ $quote->status_color }}">{{ $quote->status_label }}</span>
                </p>
                <form action="{{ route('quotes.updateStatus', $quote) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="draft"    {{ $quote->status === 'draft'    ? 'selected' : '' }}>📝 Brouillon</option>
                            <option value="sent"     {{ $quote->status === 'sent'     ? 'selected' : '' }}>📤 Envoyé</option>
                            <option value="accepted" {{ $quote->status === 'accepted' ? 'selected' : '' }}>✅ Accepté</option>
                            <option value="declined" {{ $quote->status === 'declined' ? 'selected' : '' }}>❌ Refusé</option>
                            <option value="expired"  {{ $quote->status === 'expired'  ? 'selected' : '' }}>⏰ Expiré</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-sync-alt me-1"></i>Mettre à jour le statut
                    </button>
                </form>
            </div>
        </div>
        @endif
        
        {{-- Notes --}}
        @if($quote->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0 text-muted">{!! nl2br(e($quote->notes)) !!}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
