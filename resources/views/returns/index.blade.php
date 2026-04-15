@extends('layouts.admin')

@section('title', 'Retours clients')

@section('breadcrumb')
<li class="breadcrumb-item active">Retours clients</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="page-title mb-0">Retours clients</h1>
    @role('company_admin')
    <a href="{{ route('returns.trash') }}" class="btn btn-outline-danger">
        <i class="fas fa-trash-alt me-1"></i>Corbeille
    </a>
    @endrole
</div>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-clock text-warning fs-5"></i></div>
                <div>
                    <div class="text-muted small">En attente</div>
                    <div class="fw-bold fs-4">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-box-open text-info fs-5"></i></div>
                <div>
                    <div class="text-muted small">Reçus</div>
                    <div class="fw-bold fs-4">{{ $stats['received'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check-circle text-success fs-5"></i></div>
                <div>
                    <div class="text-muted small">Résolus</div>
                    <div class="fw-bold fs-4">{{ $stats['resolved'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-3"><i class="fas fa-undo text-secondary fs-5"></i></div>
                <div>
                    <div class="text-muted small">Total</div>
                    <div class="fw-bold fs-4">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                    placeholder="N° retour, client...">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>En attente</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Reçu</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i>Filtrer</button>
                <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary ms-1">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Retour</th>
                        <th>Client</th>
                        <th>Facture</th>
                        <th>BL</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Résolution</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                    <tr>
                        <td><a href="{{ route('returns.show', $ret) }}" class="fw-semibold">{{ $ret->return_number }}</a></td>
                        <td>{{ $ret->client_name }}</td>
                        <td>
                            @if($ret->invoice)
                                <a href="{{ route('invoices.show', $ret->invoice) }}" class="small">{{ $ret->invoice->invoice_number }}</a>
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($ret->deliveryNote)
                                <a href="{{ route('deliveries.show', $ret->deliveryNote) }}" class="small">{{ $ret->deliveryNote->delivery_number }}</a>
                            @else —
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $ret->status_color }}">{{ $ret->status_label }}</span>
                        </td>
                        <td class="text-center">
                            @if($ret->resolution)
                                <span class="badge bg-light text-dark border">{{ $ret->resolution_label }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $ret->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('returns.show', $ret) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Aucun retour client</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
        <div class="p-3 border-top">{{ $returns->links() }}</div>
        @endif
    </div>
</div>
@endsection
