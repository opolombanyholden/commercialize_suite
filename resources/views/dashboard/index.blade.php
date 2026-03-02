@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('page-header')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title mb-1">Tableau de bord</h1>
        <p class="page-subtitle mb-0">Bienvenue, <strong>{{ auth()->user()->name }}</strong> &mdash; {{ now()->translatedFormat('l d F Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <select class="form-select form-select-sm" id="periodFilter" style="width: auto; min-width: 140px;">
            <option value="today">Aujourd'hui</option>
            <option value="week">Cette semaine</option>
            <option value="month" selected>Ce mois</option>
            <option value="quarter">Ce trimestre</option>
            <option value="year">Cette année</option>
        </select>
        <button class="btn btn-outline-secondary btn-sm no-loading" onclick="window.print()">
            <i class="fas fa-print me-1"></i><span class="d-none d-sm-inline">Imprimer</span>
        </button>
    </div>
</div>
@endsection

@section('content')

{{-- =================== STAT CARDS =================== --}}
<div class="row g-3 mb-4">

    {{-- Chiffre d'affaires --}}
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="overflow-hidden">
                        <p class="stat-label">Chiffre d'affaires</p>
                        <h3 class="stat-value">{{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') }}</h3>
                        <small class="text-muted">FCFA ce mois</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary flex-shrink-0">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="mt-2">
                    @php $change = $stats['revenue_change'] ?? 0; @endphp
                    @if($change >= 0)
                        <span class="badge bg-success-subtle text-success"><i class="fas fa-arrow-up me-1"></i>{{ $change }}%</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger"><i class="fas fa-arrow-down me-1"></i>{{ abs($change) }}%</span>
                    @endif
                    <span class="text-muted small ms-1 d-none d-sm-inline">vs mois précédent</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Factures --}}
    <div class="col-6 col-xl-3">
        <div class="card stat-card success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label">Factures</p>
                        <h3 class="stat-value">{{ $stats['invoices_count'] ?? 0 }}</h3>
                        <small class="text-muted">ce mois</small>
                    </div>
                    <div class="stat-icon flex-shrink-0" style="background:rgba(141,198,63,.12);color:#8DC63F;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="text-muted small">
                        <i class="fas fa-hourglass-half me-1"></i>{{ $stats['pending_quotes'] ?? 0 }} devis en attente
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Montant en attente --}}
    <div class="col-6 col-xl-3">
        <div class="card stat-card warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="overflow-hidden">
                        <p class="stat-label">En attente</p>
                        <h3 class="stat-value">{{ number_format($stats['pending_amount'] ?? 0, 0, ',', ' ') }}</h3>
                        <small class="text-muted">FCFA à encaisser</small>
                    </div>
                    <div class="stat-icon flex-shrink-0" style="background:rgba(244,123,32,.12);color:#F47B20;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="mt-2">
                    @php $overdueCount = ($overdueInvoices ?? collect())->count(); @endphp
                    @if($overdueCount > 0)
                        <span class="badge bg-danger-subtle text-danger"><i class="fas fa-exclamation me-1"></i>{{ $overdueCount }} en retard</span>
                    @else
                        <span class="badge bg-success-subtle text-success"><i class="fas fa-check me-1"></i>Aucun retard</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Clients --}}
    <div class="col-6 col-xl-3">
        <div class="card stat-card info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label">Clients actifs</p>
                        <h3 class="stat-value">{{ $stats['active_clients'] ?? 0 }}</h3>
                        <small class="text-muted">{{ $stats['products_count'] ?? 0 }} produits</small>
                    </div>
                    <div class="stat-icon flex-shrink-0" style="background:rgba(0,160,168,.12);color:#00A0A8;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="text-muted small">
                        <i class="fas fa-sync-alt me-1"></i>{{ $stats['conversion_rate'] ?? 0 }}% taux conversion
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- =================== GRAPHIQUE + DERNIERS DEVIS =================== --}}
<div class="row g-3 mb-4">

    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-area me-2 text-primary"></i>Évolution des ventes
                </h5>
                <span class="badge" style="background:rgba(233,30,140,.12);color:var(--primary-color);">6 derniers mois</span>
            </div>
            <div class="card-body">
                <div style="position:relative;height:260px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2 text-primary"></i>Derniers devis
                </h5>
                <a href="{{ route('quotes.index') }}" class="btn btn-sm btn-outline-primary no-loading">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($latestQuotes ?? [] as $quote)
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="overflow-hidden">
                                <div class="fw-semibold small text-truncate">{{ $quote->quote_number }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ Str::limit($quote->client_name ?? $quote->client?->name, 22) }}</div>
                            </div>
                            <span class="badge bg-{{ $quote->status_color ?? 'secondary' }} flex-shrink-0">
                                {{ $quote->status_label ?? $quote->status }}
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">
                        <i class="fas fa-file-alt fa-2x mb-2 d-block opacity-25"></i>
                        Aucun devis
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- =================== FACTURES + TOP CLIENTS =================== --}}
<div class="row g-3">

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2 text-primary"></i>Dernières factures
                </h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary no-loading">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th class="d-none d-md-table-cell">Client</th>
                            <th class="d-none d-sm-table-cell">Date</th>
                            <th class="text-end">Montant</th>
                            <th class="text-center">Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestInvoices ?? [] as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none small">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="d-none d-md-table-cell small text-muted">
                                {{ Str::limit($invoice->client_name ?? $invoice->client?->name, 22) }}
                            </td>
                            <td class="d-none d-sm-table-cell small text-muted">
                                {{ $invoice->invoice_date?->format('d/m/Y') }}
                            </td>
                            <td class="text-end fw-semibold small">
                                {{ number_format($invoice->total_amount, 0, ',', ' ') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $invoice->status_color ?? 'secondary' }}">
                                    {{ $invoice->status_label ?? $invoice->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-xs btn-outline-secondary no-loading" target="_blank" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block opacity-25"></i>
                                Aucune facture pour le moment
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2" style="color:#F47B20;"></i>Top clients
                </h5>
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary no-loading">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topClients ?? [] as $index => $client)
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fw-bold text-center flex-shrink-0" style="width:22px;color:{{ $index < 3 ? '#F47B20' : '#9aa1b0' }};">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold small text-truncate">{{ $client->name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $client->invoices_count }} facture(s)</div>
                            </div>
                            <span class="fw-bold small text-primary flex-shrink-0">
                                {{ number_format($client->total_revenue, 0, ',', ' ') }}
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                        Aucun client
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- =================== ALERTES =================== --}}
@if(($overdueInvoices ?? collect())->count() > 0)
<div class="alert alert-danger d-flex flex-wrap align-items-center gap-3 mt-4" role="alert">
    <i class="fas fa-exclamation-triangle fa-lg flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>{{ $overdueInvoices->count() }} facture(s) en retard</strong> —
        total <strong>{{ number_format($overdueInvoices->sum('balance'), 0, ',', ' ') }} FCFA</strong>
    </div>
    <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-danger no-loading">
        <i class="fas fa-eye me-1"></i>Voir
    </a>
</div>
@endif

@if(($lowStockProducts ?? collect())->count() > 0)
<div class="alert alert-warning d-flex flex-wrap align-items-center gap-3 mt-3" role="alert">
    <i class="fas fa-box-open fa-lg flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>{{ $lowStockProducts->count() }} produit(s)</strong> en rupture ou stock faible
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-warning no-loading">
        <i class="fas fa-warehouse me-1"></i>Voir stock
    </a>
</div>
@endif

@endsection

@push('styles')
<style>
.stat-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #9aa1b0;
    margin-bottom: 4px;
}
.stat-value {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1.1;
    color: #2e3340;
    margin-bottom: 2px;
}
.list-group-item { border-left: none; border-right: none; }
.list-group-item:first-child { border-top: none; }
@media (max-width: 575px) {
    .stat-value { font-size: 1.2rem; }
    .admin-content, .page-content { padding: 12px; }
    .row.g-3 { --bs-gutter-x: 0.5rem; --bs-gutter-y: 0.5rem; }
    .card-body { padding: 14px; }
    .card-header { padding: 12px 14px; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = {!! json_encode($salesChart['labels'] ?? ['Jan','Fév','Mar','Avr','Mai','Juin']) !!};
    const data   = {!! json_encode($salesChart['data']   ?? [0,0,0,0,0,0]) !!};
    const primary = '#E91E8C';

    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(233,30,140,0.18)');
    gradient.addColorStop(1, 'rgba(233,30,140,0.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: "Chiffre d'affaires",
                data,
                borderColor: primary,
                backgroundColor: gradient,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: c => c.parsed.y.toLocaleString('fr-FR') + ' FCFA'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { family: 'Montserrat', size: 11 },
                        callback: v => v >= 1000 ? (v/1000).toFixed(0)+'k' : v
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Montserrat', size: 11 } }
                }
            }
        }
    });

    document.getElementById('periodFilter').addEventListener('change', function () {
        window.location.href = '{{ route("dashboard") }}?period=' + this.value;
    });
});
</script>
@endpush
