@extends('layouts.admin')
@section('title', 'Corbeille — Paiements')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Paiements</a></li>
<li class="breadcrumb-item active">Corbeille</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Corbeille — Paiements</h1>
        <p class="text-muted mb-0">{{ $payments->total() }} paiement(s) supprimé(s)</p>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux paiements
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
                        <th>N° Paiement</th>
                        <th>Facture</th>
                        <th class="text-end">Montant</th>
                        <th>Méthode</th>
                        <th>Supprimé le</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="fw-semibold">{{ $payment->payment_number }}</td>
                        <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                        <td class="text-end">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $payment->method_label }}</td>
                        <td class="text-muted small">{{ $payment->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('payments.restore', $payment->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo me-1"></i>Restaurer</button>
                            </form>
                            @role('company_admin')
                            <form action="{{ route('payments.forceDelete', $payment->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer DEFINITIVEMENT ce paiement ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Supprimer</button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-trash fa-3x mb-3 d-block"></i>La corbeille est vide.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-transparent">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
