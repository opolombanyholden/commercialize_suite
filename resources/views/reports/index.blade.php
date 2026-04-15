@extends('layouts.admin')

@section('title', 'Rapports & analyses')

@section('breadcrumb')
<li class="breadcrumb-item active">Rapports</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-chart-line me-2"></i>Tableau de bord analytique</h1>
        <p class="text-muted mb-0">Vue d'ensemble & trésorerie — {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}</p>
    </div>
</div>
@endsection

@section('content')
{{-- ===== KPIs ===== --}}
<div class="row g-3 mb-4">
    {{-- CA du mois --}}
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted text-uppercase">CA du mois</small>
                    <i class="fas fa-coins text-primary"></i>
                </div>
                <h4 class="mb-1 fw-bold">{{ format_currency((float) $caMonth) }}</h4>
                @if($variationMonth !== null)
                    @php
                        $up = $variationMonth >= 0;
                        $color = $up ? 'success' : 'danger';
                        $icon = $up ? 'arrow-up' : 'arrow-down';
                    @endphp
                    <small class="text-{{ $color }}">
                        <i class="fas fa-{{ $icon }}"></i> {{ number_format(abs($variationMonth), 1, ',', ' ') }}%
                        <span class="text-muted">vs mois précédent</span>
                    </small>
                @else
                    <small class="text-muted">Pas de référence mois précédent</small>
                @endif
            </div>
        </div>
    </div>

    {{-- CA de l'année --}}
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted text-uppercase">CA cumulé année</small>
                    <i class="fas fa-calendar-alt text-info"></i>
                </div>
                <h4 class="mb-1 fw-bold">{{ format_currency((float) $caYear) }}</h4>
                <small class="text-muted">vs N-1 : {{ format_currency((float) $caPrevYearSamePeriod) }}</small>
            </div>
        </div>
    </div>

    {{-- Ticket moyen --}}
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted text-uppercase">Ticket moyen</small>
                    <i class="fas fa-receipt text-success"></i>
                </div>
                <h4 class="mb-1 fw-bold">{{ format_currency((float) $avgTicket) }}</h4>
                <small class="text-muted">{{ $invoicesIssuedMonth }} facture(s) ce mois</small>
            </div>
        </div>
    </div>

    {{-- Factures en retard --}}
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 {{ $invoicesOverdue > 0 ? 'border-start border-danger border-4' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted text-uppercase">Factures en retard</small>
                    <i class="fas fa-exclamation-triangle text-{{ $invoicesOverdue > 0 ? 'danger' : 'muted' }}"></i>
                </div>
                <h4 class="mb-1 fw-bold text-{{ $invoicesOverdue > 0 ? 'danger' : 'dark' }}">{{ $invoicesOverdue }}</h4>
                <small class="text-muted">{{ format_currency((float) $totalOverdueAmount) }} à recouvrer</small>
            </div>
        </div>
    </div>
</div>

{{-- KPIs secondaires --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body py-2 text-center">
                <small class="text-muted d-block">Factures émises (mois)</small>
                <strong class="fs-5">{{ $invoicesIssuedMonth }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body py-2 text-center">
                <small class="text-muted d-block">Factures payées (mois)</small>
                <strong class="fs-5 text-success">{{ $invoicesPaidMonth }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body py-2 text-center">
                <small class="text-muted d-block">Taux de paiement (mois)</small>
                <strong class="fs-5">
                    {{ $invoicesIssuedMonth > 0 ? round(($invoicesPaidMonth / $invoicesIssuedMonth) * 100) : 0 }}%
                </strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-light">
            <div class="card-body py-2 text-center">
                <small class="text-muted d-block">DSO (90j)</small>
                <strong class="fs-5">{{ $dso !== null ? $dso . ' jours' : '—' }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- ===== Graphiques ===== --}}
<div class="row g-4">
    {{-- Facturé vs Encaissé --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>Facturé vs Encaissé — 12 derniers mois
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 320px;">
                    <canvas id="billedVsCollectedChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Modes de paiement --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">
                    <i class="fas fa-credit-card me-2 text-primary"></i>Modes de paiement (mois)
                </h5>
            </div>
            <div class="card-body">
                @if($paymentMethods->isEmpty())
                    <p class="text-center text-muted my-5">Aucun paiement enregistré ce mois-ci.</p>
                @else
                    <div style="position: relative; height: 250px;">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    {{-- Balance âgée --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">
                    <i class="fas fa-hourglass-half me-2 text-warning"></i>Balance âgée des créances
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 220px;">
                    <canvas id="agingChart"></canvas>
                </div>
                <hr>
                <div class="row g-2 small">
                    <div class="col-6"><span class="text-muted">À échoir :</span> <strong>{{ format_currency($aging['current']) }}</strong></div>
                    <div class="col-6"><span class="text-muted">0–30 j :</span> <strong class="text-warning">{{ format_currency($aging['0_30']) }}</strong></div>
                    <div class="col-6"><span class="text-muted">30–60 j :</span> <strong class="text-warning">{{ format_currency($aging['30_60']) }}</strong></div>
                    <div class="col-6"><span class="text-muted">60–90 j :</span> <strong class="text-danger">{{ format_currency($aging['60_90']) }}</strong></div>
                    <div class="col-12"><span class="text-muted">+90 j :</span> <strong class="text-danger">{{ format_currency($aging['over_90']) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 clients à relancer --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-clock me-2 text-warning"></i>Top 10 clients à relancer
                </h5>
                <small class="text-muted">Créances ouvertes — à échoir & en retard</small>
            </div>
            <div class="card-body p-0">
                @if($topOverdueClients->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted mb-0">Aucune créance ouverte. Tout est encaissé !</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Client</th>
                                    <th class="text-center">Factures</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-end">Solde dû</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topOverdueClients as $c)
                                    <tr>
                                        <td><strong>{{ $c->client_name ?: '— Sans nom —' }}</strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $c->nb_invoices }}</span>
                                            @if($c->nb_overdue > 0)
                                                <span class="badge bg-danger" title="Factures en retard">{{ $c->nb_overdue }} en retard</span>
                                            @endif
                                        </td>
                                        <td class="text-center small">
                                            @if($c->is_overdue)
                                                @php $retard = max(0, $c->oldest_days); @endphp
                                                <span class="text-danger fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>+{{ $retard }} j
                                                </span>
                                            @elseif($c->oldest_days !== null && $c->oldest_days < 0)
                                                <span class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>Échoit dans {{ abs($c->oldest_days) }} j
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">{{ format_currency((float) $c->total_due) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fmtFCFA = (v) => new Intl.NumberFormat('fr-FR').format(Math.round(v)) + ' FCFA';

    Chart.defaults.font.family = "'Montserrat', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#6c757d';

    // ===== Facturé vs Encaissé =====
    new Chart(document.getElementById('billedVsCollectedChart'), {
        type: 'bar',
        data: {
            labels: @json($monthLabels),
            datasets: [
                {
                    label: 'Facturé',
                    data: @json($billedSeries),
                    backgroundColor: 'rgba(255, 107, 53, 0.75)',
                    borderRadius: 4,
                },
                {
                    label: 'Encaissé',
                    data: @json($collectedSeries),
                    backgroundColor: 'rgba(0, 78, 137, 0.75)',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ' : ' + fmtFCFA(ctx.parsed.y) } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) } }
            }
        }
    });

    // ===== Balance âgée =====
    const aging = @json($aging);
    new Chart(document.getElementById('agingChart'), {
        type: 'doughnut',
        data: {
            labels: ['À échoir', '0–30 j', '30–60 j', '60–90 j', '+90 j'],
            datasets: [{
                data: [aging.current, aging['0_30'], aging['30_60'], aging['60_90'], aging.over_90],
                backgroundColor: ['#10b981', '#f59e0b', '#fb923c', '#ef4444', '#7f1d1d'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8 } },
                tooltip: { callbacks: { label: (ctx) => ctx.label + ' : ' + fmtFCFA(ctx.parsed) } }
            }
        }
    });

    // ===== Modes de paiement =====
    @if($paymentMethods->isNotEmpty())
    new Chart(document.getElementById('paymentMethodsChart'), {
        type: 'doughnut',
        data: {
            labels: @json($paymentMethods->pluck('payment_method')->map(fn($m) => is_object($m) ? $m->label() : $m)),
            datasets: [{
                data: @json($paymentMethods->pluck('total')),
                backgroundColor: ['#FF6B35', '#004E89', '#10b981', '#9333EA', '#f59e0b', '#6c757d'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8 } },
                tooltip: { callbacks: { label: (ctx) => ctx.label + ' : ' + fmtFCFA(ctx.parsed) } }
            }
        }
    });
    @endif
});
</script>
@endpush
