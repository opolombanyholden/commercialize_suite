@extends('layouts.admin')

@section('title', 'Enregistrer un paiement')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Paiements</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Enregistrer un paiement</h1>
        <p class="text-muted mb-0">Enregistrer un paiement reçu</p>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
            @csrf
            
            {{-- Invoice Selection --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2 text-primary"></i>Facture concernée
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="invoice_id" class="form-label">Sélectionner une facture <span class="text-danger">*</span></label>
                        <select class="form-select @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id" required>
                            <option value="">-- Choisir une facture --</option>
                            @foreach($invoices ?? [] as $inv)
                                <option value="{{ $inv->id }}" 
                                    data-total="{{ $inv->total_amount }}"
                                    data-paid="{{ $inv->paid_amount }}"
                                    data-balance="{{ $inv->balance }}"
                                    data-client="{{ $inv->client_name }}"
                                    {{ old('invoice_id', request('invoice_id')) == $inv->id ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} - {{ $inv->client_name }} ({{ number_format($inv->balance, 0, ',', ' ') }} FCFA restant)
                                </option>
                            @endforeach
                        </select>
                        @error('invoice_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Invoice Summary (Dynamic) --}}
                    <div id="invoiceSummary" class="alert alert-info mb-0" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Montant total</small>
                                <span class="fw-semibold" id="invoiceTotal">-</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Déjà payé</small>
                                <span class="fw-semibold text-success" id="invoicePaid">-</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Reste à payer</small>
                                <span class="fw-bold text-primary" id="invoiceBalance">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Payment Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>Détails du paiement
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Amount --}}
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Montant reçu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" min="1" step="1" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <div class="form-text">
                                <a href="#" id="fillBalance" style="display: none;">Remplir avec le solde restant</a>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Payment Date --}}
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">Date du paiement <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Payment Method --}}
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                <option value="">-- Choisir --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                    💵 Espèces
                                </option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                                    🏦 Virement bancaire
                                </option>
                                <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>
                                    📝 Chèque
                                </option>
                                <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>
                                    📱 Mobile Money (Airtel/Moov)
                                </option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>
                                    💳 Carte bancaire
                                </option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Reference --}}
                        <div class="col-md-6">
                            <label for="reference" class="form-label">Référence / N° transaction</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}" placeholder="N° chèque, référence virement...">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Notes --}}
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Notes internes sur ce paiement...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check me-2"></i>Enregistrer le paiement
                </button>
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-lg">
                    Annuler
                </a>
            </div>
        </form>
    </div>
    
    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Help Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-info"></i>Aide</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>Paiement partiel :</strong> Vous pouvez enregistrer un montant inférieur au solde. La facture passera en statut "Partiellement payée".
                </p>
                <p class="small text-muted mb-2">
                    <strong>Paiement total :</strong> Si le montant couvre le solde restant, la facture sera automatiquement marquée comme "Payée".
                </p>
                <p class="small text-muted mb-0">
                    <strong>Référence :</strong> Pour les virements et chèques, notez la référence pour faciliter la réconciliation bancaire.
                </p>
            </div>
        </div>
        
        {{-- Recent Payments --}}
        @if(isset($recentPayments) && $recentPayments->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-history me-2 text-muted"></i>Paiements récents</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($recentPayments as $payment)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">{{ $payment->payment_date->format('d/m') }}</small>
                            <br>{{ $payment->invoice->invoice_number }}
                        </div>
                        <span class="badge bg-success">+{{ number_format($payment->amount, 0, ',', ' ') }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoice_id');
    const summaryDiv = document.getElementById('invoiceSummary');
    const amountInput = document.getElementById('amount');
    const fillBalanceLink = document.getElementById('fillBalance');
    
    let currentBalance = 0;
    
    invoiceSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        
        if (option.value) {
            const total = parseFloat(option.dataset.total);
            const paid = parseFloat(option.dataset.paid);
            const balance = parseFloat(option.dataset.balance);
            
            currentBalance = balance;
            
            document.getElementById('invoiceTotal').textContent = formatNumber(total) + ' FCFA';
            document.getElementById('invoicePaid').textContent = formatNumber(paid) + ' FCFA';
            document.getElementById('invoiceBalance').textContent = formatNumber(balance) + ' FCFA';
            
            summaryDiv.style.display = 'block';
            fillBalanceLink.style.display = 'inline';
            
            // Pre-fill amount if empty
            if (!amountInput.value) {
                amountInput.value = balance;
            }
        } else {
            summaryDiv.style.display = 'none';
            fillBalanceLink.style.display = 'none';
            currentBalance = 0;
        }
    });
    
    fillBalanceLink.addEventListener('click', function(e) {
        e.preventDefault();
        amountInput.value = currentBalance;
    });
    
    // Trigger on load if invoice is pre-selected
    if (invoiceSelect.value) {
        invoiceSelect.dispatchEvent(new Event('change'));
    }
    
    function formatNumber(num) {
        return Math.round(num).toLocaleString('fr-FR');
    }
});
</script>
@endpush
