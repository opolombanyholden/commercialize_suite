@extends('layouts.admin')

@section('title', 'Factures')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Factures</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-file-invoice-dollar me-2"></i>Factures</h1>
            <p class="page-subtitle">{{ $invoices->total() }} factures</p>
        </div>
        <div class="col-auto">
            @can('invoices.create')
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle facture
            </a>
            @endcan
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-primary">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-mini-content">
                <h4>{{ $stats['total'] ?? 0 }}</h4>
                <p>Total factures</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-mini-content">
                <h4>{{ number_format($stats['paid_amount'] ?? 0, 0, ',', ' ') }}</h4>
                <p>Encaissé (FCFA)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-mini-content">
                <h4>{{ number_format($stats['pending_amount'] ?? 0, 0, ',', ' ') }}</h4>
                <p>En attente (FCFA)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-danger">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-mini-content">
                <h4>{{ $stats['overdue'] ?? 0 }}</h4>
                <p>En retard</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('invoices.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="N° facture, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Statut</option>
                    @foreach(\App\Enums\InvoiceStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="Du">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="Au">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-search me-2"></i>Filtrer
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th class="text-end">Montant</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $invoice->client_name }}</div>
                            @if($invoice->client)
                                <small class="text-muted">{{ $invoice->client->email }}</small>
                            @endif
                        </td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            @if($invoice->due_date)
                                <span class="{{ $invoice->due_date->isPast() && $invoice->payment_status !== 'paid' ? 'text-danger' : '' }}">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($invoice->total_amount, 0, ',', ' ') }}</strong> FCFA
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->status->color() }}">
                                {{ $invoice->status->label() }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->payment_status === 'paid')
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Payée</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge bg-warning">Partiel</span>
                                <br><small>{{ number_format($invoice->paid_amount ?? 0, 0, ',', ' ') }} / {{ number_format($invoice->total_amount, 0, ',', ' ') }}</small>
                            @else
                                <span class="badge bg-secondary">Non payée</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-danger" title="PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @can('invoices.edit')
                                @if($invoice->status->value === 'draft')
                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endcan
                                @can('payments.create')
                                @if($invoice->payment_status !== 'paid')
                                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success" title="Paiement">
                                    <i class="fas fa-money-bill"></i>
                                </a>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                            Aucune facture trouvée
                            @can('invoices.create')
                            <br><a href="{{ route('invoices.create') }}" class="btn btn-primary mt-3">Créer une facture</a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
.stat-mini-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.stat-mini-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}
.stat-mini-content h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}
.stat-mini-content p {
    margin: 0;
    color: #666;
    font-size: 13px;
}
</style>
@endpush
