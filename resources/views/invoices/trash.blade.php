@extends('layouts.admin')
@section('title', 'Corbeille — Factures')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Factures</a></li>
<li class="breadcrumb-item active">Corbeille</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Corbeille — Factures</h1>
        <p class="text-muted mb-0">{{ $invoices->total() }} facture(s) supprimée(s)</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux factures
    </a>
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Supprimée le</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                        <td>{{ Str::limit($invoice->client_name, 30) }}</td>
                        <td>{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                        <td><span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span></td>
                        <td class="text-muted small">{{ $invoice->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('invoices.restore', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Restaurer">
                                    <i class="fas fa-undo me-1"></i>Restaurer
                                </button>
                            </form>
                            @role('company_admin')
                            <form action="{{ route('invoices.forceDelete', $invoice->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer DEFINITIVEMENT cette facture ? Cette action est irréversible.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer définitivement">
                                    <i class="fas fa-times me-1"></i>Supprimer
                                </button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-trash fa-3x mb-3 d-block"></i>
                            La corbeille est vide.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer bg-transparent">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
