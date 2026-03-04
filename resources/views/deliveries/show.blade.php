@extends('layouts.admin')

@section('title', 'BL ' . $delivery->delivery_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">Bons de livraison</a></li>
<li class="breadcrumb-item active">{{ $delivery->delivery_number }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Main --}}
    <div class="col-lg-8">

        {{-- Header --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ $delivery->delivery_number }}</h3>
                        <p class="text-muted mb-0">
                            Créé le {{ $delivery->created_at->format('d/m/Y') }}
                            @if($delivery->invoice)
                                &nbsp;•&nbsp; Facture : <a href="{{ route('invoices.show', $delivery->invoice) }}">{{ $delivery->invoice->invoice_number }}</a>
                            @endif
                        </p>
                    </div>
                    <span class="badge bg-{{ $delivery->status_color }} fs-6">{{ $delivery->status_label }}</span>
                </div>
            </div>
        </div>

        {{-- Client & Dates --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Client</h6>
                    </div>
                    <div class="card-body">
                        <p class="fw-semibold mb-1">{{ $delivery->client_name }}</p>
                        @if($delivery->client_email)
                            <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $delivery->client_email }}</p>
                        @endif
                        @if($delivery->client_phone)
                            <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i>{{ $delivery->client_phone }}</p>
                        @endif
                        @if($delivery->delivery_address)
                            <p class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-2"></i>{{ $delivery->delivery_address }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-calendar me-2 text-primary"></i>Dates & Livreur</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Date prévue</span>
                            <span class="fw-semibold">{{ $delivery->planned_date->format('d/m/Y') }}</span>
                        </div>
                        @if($delivery->delivered_date)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Date effective</span>
                            <span class="fw-semibold text-success">{{ $delivery->delivered_date->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($delivery->livreur)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Livreur</span>
                            <span class="fw-semibold">{{ $delivery->livreur }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Articles --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Articles</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    @php $recap = $delivery->getDeliveryRecap(); $hasInvoice = $delivery->invoice_id && !empty($recap); @endphp
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                @if($hasInvoice)
                                    <th class="text-center" title="Quantité commandée dans la facture">Commandé</th>
                                    <th class="text-center" title="Livré dans les BL précédents">Déjà livré</th>
                                @endif
                                <th class="text-center">Cette livraison</th>
                                @if($hasInvoice)
                                    <th class="text-center" title="Quantité encore à livrer après ce BL">Reste</th>
                                @endif
                                <th>Unité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($hasInvoice)
                                @foreach($recap as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row['description'] }}</td>
                                    <td class="text-center text-muted">{{ $row['ordered'] !== null ? number_format($row['ordered'], 2) : '—' }}</td>
                                    <td class="text-center text-success">{{ number_format($row['already_delivered'], 2) }}</td>
                                    <td class="text-center fw-semibold">{{ number_format($row['this_delivery'], 2) }}</td>
                                    <td class="text-center {{ ($row['remaining'] ?? 0) > 0 ? 'text-warning fw-semibold' : 'text-success' }}">
                                        {{ $row['remaining'] !== null ? number_format($row['remaining'], 2) : '—' }}
                                    </td>
                                    <td>{{ $row['unit'] ?? '—' }}</td>
                                </tr>
                                @endforeach
                            @else
                                @foreach($delivery->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center fw-semibold">{{ number_format($item->quantity, 2) }}</td>
                                    <td>{{ $item->unit ?? '—' }}</td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                        @if($hasInvoice)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end text-muted">Totaux</td>
                                <td class="text-center text-muted fw-semibold">{{ number_format(collect($recap)->sum(fn($r) => $r['ordered'] ?? 0), 2) }}</td>
                                <td class="text-center text-success fw-semibold">{{ number_format(collect($recap)->sum('already_delivered'), 2) }}</td>
                                <td class="text-center fw-bold">{{ number_format(collect($recap)->sum('this_delivery'), 2) }}</td>
                                <td class="text-center fw-semibold {{ collect($recap)->sum('remaining') > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ number_format(collect($recap)->sum(fn($r) => $r['remaining'] ?? 0), 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Confirmation PIN (si déjà vérifié) --}}
        @if($delivery->isPinVerified())
        <div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 flex-shrink-0">
                    <i class="fas fa-check-circle text-success fs-4"></i>
                </div>
                <div>
                    <div class="fw-semibold text-success">Livraison confirmée par PIN</div>
                    <div class="small text-muted">
                        Confirmé par {{ $delivery->pin_verified_by === 'client' ? 'le client' : 'le livreur' }}
                        le {{ $delivery->pin_verified_at->format('d/m/Y à H:i') }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Signature --}}
        @if($delivery->isDelivered() && $delivery->signature)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-signature me-2 text-primary"></i>Signature de réception</h6>
            </div>
            <div class="card-body text-center">
                <img src="{{ $delivery->signature }}" alt="Signature" class="border rounded" style="max-height:150px; background:#f8f9fa; padding:10px;">
            </div>
        </div>
        @elseif(!$delivery->isDelivered() && !$delivery->isCancelled())
        @can('deliveries.edit')

        {{-- Vérification par PIN (livreur) --}}
        @if($delivery->invoice?->hasDeliveryPin())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-key me-2 text-warning"></i>Valider par code PIN</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Saisissez le code que le client vous communique (visible sur sa facture).</p>
                <form action="{{ route('deliveries.verifyPin', $delivery) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="pin"
                            class="form-control text-center @error('pin') is-invalid @enderror"
                            placeholder="XXXXXXXX"
                            maxlength="8"
                            style="letter-spacing:.3em; font-size:1.2rem; font-family:monospace; text-transform:uppercase;"
                            autocomplete="off">
                        @error('pin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-check me-2"></i>Valider la livraison
                    </button>
                </form>
                <div class="text-center mt-2">
                    <small class="text-muted">— ou —</small>
                </div>
            </div>
        </div>
        @endif

        {{-- Capture signature --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-pen me-2 text-primary"></i>Signature de réception</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Faire signer le destinataire pour confirmer la livraison.</p>
                <canvas id="signatureCanvas" width="500" height="150"
                    class="border rounded w-100" style="background:#f8f9fa; touch-action:none; cursor:crosshair;"></canvas>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSignature">
                        <i class="fas fa-eraser me-1"></i>Effacer
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="saveSignature">
                        <i class="fas fa-check me-1"></i>Valider la livraison
                    </button>
                </div>
                <form action="{{ route('deliveries.signature', $delivery) }}" method="POST" id="signatureForm">
                    @csrf
                    <input type="hidden" name="signature" id="signatureData">
                </form>
            </div>
        </div>
        @endcan
        @endif

        @if($delivery->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0 text-muted">{!! nl2br(e($delivery->notes)) !!}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">

        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('deliveries.pdf', $delivery) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                    </a>

                    @if($delivery->isDelivered())
                    @can('returns.create')
                    <a href="{{ route('returns.create', ['delivery_note_id' => $delivery->id]) }}" class="btn btn-outline-warning">
                        <i class="fas fa-undo me-2"></i>Créer un retour client
                    </a>
                    @endcan
                    @endif

                    @if(!$delivery->isDelivered() && !$delivery->isCancelled())
                        @can('deliveries.edit')
                        <a href="{{ route('deliveries.edit', $delivery) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        @endcan
                    @endif

                    @can('deliveries.delete')
                    <form action="{{ route('deliveries.destroy', $delivery) }}" method="POST" onsubmit="return confirm('Supprimer ce bon de livraison ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Status Update --}}
        @if(!$delivery->isDelivered())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Changer le statut</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    Statut actuel : <span class="badge bg-{{ $delivery->status_color }}">{{ $delivery->status_label }}</span>
                </p>
                <form action="{{ route('deliveries.updateStatus', $delivery) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="pending"    {{ $delivery->status === 'pending'    ? 'selected' : '' }}>⏳ En attente</option>
                            <option value="in_transit" {{ $delivery->status === 'in_transit' ? 'selected' : '' }}>🚚 En transit</option>
                            <option value="delivered"  {{ $delivery->status === 'delivered'  ? 'selected' : '' }}>✅ Livré</option>
                            <option value="cancelled"  {{ $delivery->status === 'cancelled'  ? 'selected' : '' }}>❌ Annulé</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-sync-alt me-1"></i>Mettre à jour
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Summary --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Récapitulatif</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Nombre d'articles</span>
                    <span class="fw-semibold">{{ $delivery->items->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Quantité totale</span>
                    <span class="fw-semibold">{{ number_format($delivery->items->sum('quantity'), 2) }}</span>
                </div>
                @if($delivery->isDelivered() && $delivery->delivered_date)
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Livré le</span>
                    <span class="fw-semibold text-success">{{ $delivery->delivered_date->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }

    canvas.addEventListener('mousedown',  e => { drawing = true; ctx.beginPath(); ctx.moveTo(...Object.values(getPos(e))); });
    canvas.addEventListener('mousemove',  e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#000'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke(); });
    canvas.addEventListener('mouseup',    () => { drawing = false; });
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; ctx.beginPath(); ctx.moveTo(...Object.values(getPos(e))); });
    canvas.addEventListener('touchmove',  e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#000'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke(); });
    canvas.addEventListener('touchend',   () => { drawing = false; });

    document.getElementById('clearSignature').addEventListener('click', () => ctx.clearRect(0, 0, canvas.width, canvas.height));

    document.getElementById('saveSignature').addEventListener('click', function () {
        const data = canvas.toDataURL('image/png');
        if (data === canvas.toDataURL('image/png', 0)) { alert('Veuillez signer avant de valider.'); return; }
        document.getElementById('signatureData').value = data;
        document.getElementById('signatureForm').submit();
    });
})();
</script>
@endpush
