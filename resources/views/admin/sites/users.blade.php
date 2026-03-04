@extends('layouts.admin')

@section('title', 'Utilisateurs — ' . $site->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sites.index') }}">Sites</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.sites.show', $site) }}">{{ $site->name }}</a></li>
<li class="breadcrumb-item active">Utilisateurs</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-users me-2"></i>Utilisateurs du site</h1>
        <p class="text-muted mb-0">{{ $site->name }}</p>
    </div>
    <a href="{{ route('admin.sites.show', $site) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">

    {{-- Users list --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Utilisateurs assignés ({{ $site->users->count() }})</h6>
            </div>
            @if($site->users->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-user-slash fa-3x mb-3"></i>
                <p>Aucun utilisateur assigné à ce site</p>
            </div>
            @else
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Email</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($site->users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary">
                                        <span class="avatar-text">{{ substr($user->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        @if(!$user->is_active)
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-light text-dark border">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-muted small">{{ $user->email }}</td>
                            <td>
                                <form action="{{ route('admin.sites.users.remove', [$site, $user]) }}" method="POST"
                                      onsubmit="return confirm('Retirer {{ $user->name }} de ce site ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Add user --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Ajouter un utilisateur</h6>
            </div>
            <div class="card-body">
                @if($availableUsers->isEmpty())
                    <p class="text-muted text-center mb-0">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Tous les utilisateurs actifs sont déjà assignés à ce site.
                    </p>
                @else
                <form action="{{ route('admin.sites.users.add', $site) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Sélectionner un utilisateur</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id" required>
                            <option value="">-- Choisir --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} — {{ $user->roles->first()?->name ?? 'Aucun rôle' }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Ajouter
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
