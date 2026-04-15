@extends('layouts.admin')
@section('title', 'Corbeille — Retours clients')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Retours clients</a></li>
<li class="breadcrumb-item active">Corbeille</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Corbeille — Retours clients</h1>
        <p class="text-muted mb-0">{{ $returns->total() }} retour(s) supprimé(s)</p>
    </div>
    <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux retours
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
                        <th>N° Retour</th>
                        <th>Client</th>
                        <th>Facture</th>
                        <th>Statut</th>
                        <th>Supprimé le</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td class="fw-semibold">{{ $return->return_number }}</td>
                        <td>{{ Str::limit($return->client_name, 30) }}</td>
                        <td>{{ $return->invoice?->invoice_number ?? '—' }}</td>
                        <td><span class="badge bg-{{ $return->status_color }}">{{ $return->status_label }}</span></td>
                        <td class="text-muted small">{{ $return->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('returns.restore', $return->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo me-1"></i>Restaurer</button>
                            </form>
                            @role('company_admin')
                            <form action="{{ route('returns.forceDelete', $return->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer DEFINITIVEMENT ce retour ?')">
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
    @if($returns->hasPages())
    <div class="card-footer bg-transparent">{{ $returns->links() }}</div>
    @endif
</div>
@endsection
