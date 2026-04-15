@extends('layouts.admin')
@section('title', 'Corbeille — Devis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Devis</a></li>
<li class="breadcrumb-item active">Corbeille</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Corbeille — Devis</h1>
        <p class="text-muted mb-0">{{ $quotes->total() }} devis supprimé(s)</p>
    </div>
    <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux devis
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
                        <th>N° Devis</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Supprimé le</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                    <tr>
                        <td class="fw-semibold">{{ $quote->quote_number }}</td>
                        <td>{{ Str::limit($quote->client_name, 30) }}</td>
                        <td>{{ number_format($quote->total_amount, 0, ',', ' ') }} FCFA</td>
                        <td class="text-muted small">{{ $quote->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('quotes.restore', $quote->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Restaurer">
                                    <i class="fas fa-undo me-1"></i>Restaurer
                                </button>
                            </form>
                            @role('company_admin')
                            <form action="{{ route('quotes.forceDelete', $quote->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer DEFINITIVEMENT ce devis ? Cette action est irréversible.')">
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
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-trash fa-3x mb-3 d-block"></i>
                            La corbeille est vide.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($quotes->hasPages())
    <div class="card-footer bg-transparent">{{ $quotes->links() }}</div>
    @endif
</div>
@endsection
