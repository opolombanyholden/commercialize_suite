@extends('layouts.admin')
@section('title', 'Corbeille — Inventaires')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stocks</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.sessions.index') }}">Inventaires</a></li>
<li class="breadcrumb-item active">Corbeille</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Corbeille — Inventaires</h1>
        <p class="text-muted mb-0">{{ $sessions->total() }} inventaire(s) supprimé(s)</p>
    </div>
    <a href="{{ route('inventory.sessions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux inventaires
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
                        <th>Nom</th>
                        <th>Site</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Supprimé le</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td class="fw-semibold">{{ $session->name }}</td>
                        <td>{{ $session->site?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $session->status === 'completed' ? 'success' : 'warning' }}">
                                {{ $session->status === 'completed' ? 'Clôturé' : 'En cours' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $session->date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-muted small">{{ $session->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('inventory.sessions.restore', $session->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo me-1"></i>Restaurer</button>
                            </form>
                            @role('company_admin')
                            <form action="{{ route('inventory.sessions.forceDelete', $session->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer DEFINITIVEMENT cet inventaire ?')">
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
    @if($sessions->hasPages())
    <div class="card-footer bg-transparent">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
