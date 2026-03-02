@extends('layouts.admin')

@section('title', 'Gestion des taxes')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Paramètres</a></li>
<li class="breadcrumb-item active">Taxes</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Gestion des taxes</h1>
        <p class="text-muted mb-0">Configurez les taxes applicables à vos documents</p>
    </div>
    @can('taxes.manage')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taxModal" onclick="resetTaxForm()">
        <i class="fas fa-plus me-2"></i>Nouvelle taxe
    </button>
    @endcan
</div>
@endsection

@section('content')
{{-- Info Card --}}
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-info-circle fa-2x"></i>
        </div>
        <div>
            <h6 class="alert-heading mb-1">Taxes au Gabon</h6>
            <p class="mb-0 small">
                La TPS (Taxe sur la Prestation de Services) est la principale taxe applicable au Gabon.
                Le taux standard est de <strong>18%</strong>. Certains produits et services peuvent bénéficier d'un taux réduit de <strong>10%</strong> ou être exonérés.
            </p>
        </div>
    </div>
</div>

{{-- Taxes Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom de la taxe</th>
                        <th class="text-center">Taux</th>
                        <th class="text-center">S'applique à</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Par défaut</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $tax->name }}</span>
                            @if($tax->description)
                                <br><small class="text-muted">{{ $tax->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ number_format($tax->rate, 2) }}%</span>
                        </td>
                        <td class="text-center">
                            @if($tax->apply_to === 'all')
                                <span class="badge bg-secondary">Tous</span>
                            @elseif($tax->apply_to === 'products')
                                <span class="badge bg-warning">Produits uniquement</span>
                            @else
                                <span class="badge bg-info">Services uniquement</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($tax->is_active)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Active</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($tax->is_default)
                                <i class="fas fa-star text-warning" title="Taxe par défaut"></i>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @can('taxes.manage')
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="editTax({{ json_encode($tax) }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if(!$tax->is_system)
                                <form action="{{ route('taxes.destroy', $tax) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette taxe ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-percent fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucune taxe configurée</p>
                            @can('taxes.manage')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taxModal">
                                <i class="fas fa-plus me-2"></i>Créer la première taxe
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Preset Buttons --}}
@can('taxes.manage')
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent">
        <h6 class="card-title mb-0"><i class="fas fa-magic me-2"></i>Taxes prédéfinies (Gabon)</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Cliquez pour ajouter rapidement une taxe prédéfinie :</p>
        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('taxes.store') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="name" value="TPS Standard">
                <input type="hidden" name="rate" value="18">
                <input type="hidden" name="apply_to" value="all">
                <input type="hidden" name="description" value="Taxe sur la Prestation de Services - Taux normal">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>TPS 18%
                </button>
            </form>
            
            <form action="{{ route('taxes.store') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="name" value="TPS Réduite">
                <input type="hidden" name="rate" value="10">
                <input type="hidden" name="apply_to" value="all">
                <input type="hidden" name="description" value="Taxe sur la Prestation de Services - Taux réduit">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-plus me-1"></i>TPS 10%
                </button>
            </form>
            
            <form action="{{ route('taxes.store') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="name" value="Exonéré">
                <input type="hidden" name="rate" value="0">
                <input type="hidden" name="apply_to" value="all">
                <input type="hidden" name="description" value="Produits et services exonérés de taxe">
                <button type="submit" class="btn btn-outline-success">
                    <i class="fas fa-plus me-1"></i>Exonéré 0%
                </button>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Tax Modal --}}
<div class="modal fade" id="taxModal" tabindex="-1" aria-labelledby="taxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="taxForm" method="POST" action="{{ route('taxes.store') }}">
                @csrf
                <div id="taxMethodField"></div>
                
                <div class="modal-header">
                    <h5 class="modal-title" id="taxModalLabel">
                        <i class="fas fa-percent me-2"></i>Nouvelle taxe
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                
                <div class="modal-body">
                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="taxName" class="form-label">Nom de la taxe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="taxName" name="name" required maxlength="50" placeholder="Ex: TPS Standard">
                    </div>
                    
                    {{-- Rate --}}
                    <div class="mb-3">
                        <label for="taxRate" class="form-label">Taux (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="taxRate" name="rate" required min="0" max="100" step="0.01" placeholder="18">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    
                    {{-- Apply To --}}
                    <div class="mb-3">
                        <label for="taxApplyTo" class="form-label">S'applique à</label>
                        <select class="form-select" id="taxApplyTo" name="apply_to">
                            <option value="all">Tous (produits et services)</option>
                            <option value="products">Produits uniquement</option>
                            <option value="services">Services uniquement</option>
                        </select>
                    </div>
                    
                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="taxDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taxDescription" name="description" rows="2" maxlength="255"></textarea>
                    </div>
                    
                    {{-- Options --}}
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="taxIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="taxIsActive">
                                    Taxe active
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="taxIsDefault" name="is_default" value="1">
                                <label class="form-check-label" for="taxIsDefault">
                                    Taxe par défaut
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="taxSubmitBtn">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const taxModal = new bootstrap.Modal(document.getElementById('taxModal'));
const taxForm = document.getElementById('taxForm');
const taxModalTitle = document.getElementById('taxModalLabel');
const taxMethodField = document.getElementById('taxMethodField');
const taxSubmitBtn = document.getElementById('taxSubmitBtn');

function resetTaxForm() {
    taxForm.action = '{{ route("taxes.store") }}';
    taxForm.reset();
    taxModalTitle.innerHTML = '<i class="fas fa-percent me-2"></i>Nouvelle taxe';
    taxMethodField.innerHTML = '';
    taxSubmitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
    document.getElementById('taxIsActive').checked = true;
    document.getElementById('taxIsDefault').checked = false;
}

function editTax(tax) {
    taxForm.action = '/settings/taxes/' + tax.id;
    taxMethodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
    taxModalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Modifier la taxe';
    taxSubmitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Mettre à jour';
    
    document.getElementById('taxName').value = tax.name || '';
    document.getElementById('taxRate').value = tax.rate || 0;
    document.getElementById('taxApplyTo').value = tax.apply_to || 'all';
    document.getElementById('taxDescription').value = tax.description || '';
    document.getElementById('taxIsActive').checked = tax.is_active == 1;
    document.getElementById('taxIsDefault').checked = tax.is_default == 1;
    
    taxModal.show();
}

document.getElementById('taxModal').addEventListener('hidden.bs.modal', function() {
    resetTaxForm();
});
</script>
@endpush
