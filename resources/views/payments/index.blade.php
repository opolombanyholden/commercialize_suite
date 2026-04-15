@extends('layouts.admin')

@section('title', 'Paiements')

@section('breadcrumb')
<li class="breadcrumb-item active">Paiements</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Paiements</h1>
        <p class="text-muted mb-0">{{ $payments->total() }} paiement(s) enregistré(s)</p>
    </div>
    <div class="d-flex gap-2">
        @role('company_admin')
        <a href="{{ route('payments.trash') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash-alt me-1"></i>Corbeille
        </a>
        @endrole
        @can('payments.create')
        <a href="{{ route('payments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau paiement
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total encaissé (ce mois)</p>
                        <h4 class="mb-0 text-success">{{ number_format($stats['month_total'] ?? 0, 0, ',', ' ') }}</h4>
                        <small class="text-muted">FCFA</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total encaissé (année)</p>
                        <h4 class="mb-0 text-primary">{{ number_format($stats['year_total'] ?? 0, 0, ',', ' ') }}</h4>
                        <small class="text-muted">FCFA</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Nombre de paiements</p>
                        <h4 class="mb-0 text-info">{{ $stats['count'] ?? 0 }}</h4>
                        <small class="text-muted">ce mois</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('payments.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="N° facture, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="method">
                    <option value="">Toutes méthodes</option>
                    <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="bank_transfer" {{ request('method') == 'bank_transfer' ? 'selected' : '' }}>Virement</option>
                    <option value="check" {{ request('method') == 'check' ? 'selected' : '' }}>Chèque</option>
                    <option value="mobile_money" {{ request('method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="card" {{ request('method') == 'card' ? 'selected' : '' }}>Carte bancaire</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Payments Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Facture</th>
                        <th>Client</th>
                        <th class="text-center">Méthode</th>
                        <th>Référence</th>
                        <th class="text-end">Montant</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $payment->payment_date->format('d/m/Y') }}</span>
                            <br><small class="text-muted">{{ $payment->payment_date->format('H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-decoration-none fw-semibold">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        </td>
                        <td>{{ Str::limit($payment->invoice->client_name, 25) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $payment->method_color }}">
                                <i class="fas fa-{{ $payment->method_icon }} me-1"></i>{{ $payment->method_label }}
                            </span>
                        </td>
                        <td>
                            <code>{{ $payment->reference ?? '-' }}</code>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-success">+{{ number_format($payment->amount, 0, ',', ' ') }}</span>
                            <br><small class="text-muted">FCFA</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('payments.receipt', $payment) }}" target="_blank">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>Reçu PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('invoices.show', $payment->invoice_id) }}">
                                            <i class="fas fa-file-invoice me-2 text-muted"></i>Voir facture
                                        </a>
                                    </li>
                                    @can('payments.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Supprimer ce paiement ? Cette action est irréversible.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucun paiement enregistré</p>
                            @can('payments.create')
                            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Enregistrer un paiement
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($payments->hasPages())
    <div class="card-footer bg-transparent">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
</style>
@endpush
