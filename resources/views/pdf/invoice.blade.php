<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #FF6B35;
            padding-bottom: 15px;
        }
        .header-left, .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            text-align: right;
        }
        .logo {
            max-width: 150px;
            max-height: 60px;
        }
        .company-info {
            margin-top: 8px;
            font-size: 9pt;
            color: #666;
        }
        .doc-title {
            font-size: 28pt;
            font-weight: bold;
            color: #FF6B35;
            margin-bottom: 5px;
        }
        .doc-number {
            font-size: 11pt;
            color: #666;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        .info-box.client {
            background-color: #f8f9fa;
            border-left: 4px solid #FF6B35;
        }
        .info-label {
            font-size: 8pt;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .info-value.highlight {
            font-weight: bold;
            font-size: 11pt;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.items thead {
            background-color: #004E89;
            color: white;
        }
        table.items th {
            padding: 10px 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
        }
        table.items th.right {
            text-align: right;
        }
        table.items th.center {
            text-align: center;
        }
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9pt;
        }
        table.items td.right {
            text-align: right;
        }
        table.items td.center {
            text-align: center;
        }
        table.items tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8pt;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .type-badge.product {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .totals {
            width: 45%;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals-row {
            display: table;
            width: 100%;
            padding: 5px 0;
        }
        .totals-label, .totals-value {
            display: table-cell;
        }
        .totals-label {
            text-align: right;
            padding-right: 15px;
            color: #666;
        }
        .totals-value {
            text-align: right;
            width: 120px;
        }
        .totals-row.grand {
            border-top: 2px solid #004E89;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 14pt;
            font-weight: bold;
            color: #FF6B35;
        }
        .amount-words {
            margin-top: 25px;
            padding: 12px;
            background-color: #f5f5f5;
            border-left: 4px solid #FF6B35;
            font-style: italic;
            font-size: 9pt;
        }
        .notes {
            margin-top: 20px;
            padding: 12px;
            background-color: #fffbf0;
            border: 1px solid #ffe0b2;
            font-size: 9pt;
        }
        .payment-info {
            margin-top: 20px;
            padding: 12px;
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
        }
        .status-paid {
            text-align: center;
            padding: 15px;
            background-color: #4caf50;
            color: white;
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
        }
        .status-partial {
            text-align: center;
            padding: 15px;
            background-color: #ff9800;
            color: white;
            margin-top: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 1.5cm;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #999;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .footer-left, .footer-right {
            display: table-cell;
        }
        .footer-right {
            text-align: right;
        }
        .watermark {
            position: fixed;
            top: 45%;
            left: 25%;
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.04);
            transform: rotate(-45deg);
            z-index: -1;
        }
    </style>
</head>
<body>
    @if($invoice->status === 'draft')
        <div class="watermark">BROUILLON</div>
    @elseif($invoice->status === 'cancelled')
        <div class="watermark">ANNULÉE</div>
    @endif

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if($company->logo_path)
                <img src="{{ public_path('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="logo">
            @endif
            <div class="company-info">
                <strong>{{ $company->name }}</strong><br>
                @if($company->legal_name){{ $company->legal_name }}<br>@endif
                {{ $company->address }}<br>
                {{ $company->postal_code }} {{ $company->city }}<br>
                @if($company->phone)Tél: {{ $company->phone }}<br>@endif
                @if($company->email){{ $company->email }}<br>@endif
                @if($company->tax_id)N° Fiscal: {{ $company->tax_id }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">FACTURE</div>
            <div class="doc-number">N° {{ $invoice->invoice_number }}</div>
        </div>
    </div>

    {{-- Info Section --}}
    <div class="info-section">
        <div class="info-box client">
            <div class="info-label">Facturé à</div>
            <div class="info-value highlight">{{ $invoice->client_name }}</div>
            @if($invoice->client_address)
                <div class="info-value">{{ $invoice->client_address }}</div>
            @endif
            @if($invoice->client_email)
                <div class="info-value">{{ $invoice->client_email }}</div>
            @endif
            @if($invoice->client_phone)
                <div class="info-value">{{ $invoice->client_phone }}</div>
            @endif
        </div>
        <div class="info-box" style="text-align: right;">
            <div class="info-label">Date de facturation</div>
            <div class="info-value highlight">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
            <div class="info-label" style="margin-top: 10px;">Date d'échéance</div>
            <div class="info-value highlight" style="{{ $invoice->is_overdue ? 'color: #d32f2f;' : '' }}">
                {{ $invoice->due_date->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Description</th>
                <th class="center" style="width: 12%;">Type</th>
                <th class="center" style="width: 10%;">Qté</th>
                <th class="right" style="width: 15%;">P.U. HT</th>
                <th class="right" style="width: 18%;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="center">
                    <span class="type-badge {{ $item->type === 'product' ? 'product' : '' }}">
                        {{ $item->type === 'service' ? 'Service' : 'Produit' }}
                    </span>
                </td>
                <td class="center">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                <td class="right"><strong>{{ number_format($item->total, 0, ',', ' ') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Sous-total HT</div>
            <div class="totals-value">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</div>
        </div>
        @foreach($invoice->taxes as $tax)
        <div class="totals-row">
            <div class="totals-label">{{ $tax->tax_name }} ({{ $tax->tax_rate }}%)</div>
            <div class="totals-value">{{ number_format($tax->tax_amount, 0, ',', ' ') }} FCFA</div>
        </div>
        @endforeach
        <div class="totals-row grand">
            <div class="totals-label">Total TTC</div>
            <div class="totals-value">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    {{-- Amount in Words --}}
    <div class="amount-words">
        <strong>Arrêté la présente facture à la somme de :</strong><br>
        {{ $invoice->total_in_words ?? number_to_words($invoice->total_amount) }} francs CFA
    </div>

    {{-- Payment Status --}}
    @if($invoice->payment_status === 'paid')
        <div class="status-paid">
            ✓ FACTURE PAYÉE
            @if($invoice->last_payment_date)
                - Le {{ $invoice->last_payment_date->format('d/m/Y') }}
            @endif
        </div>
    @elseif($invoice->payment_status === 'partial')
        <div class="status-partial">
            <strong>PAIEMENT PARTIEL</strong><br>
            Payé: {{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA |
            Reste: {{ number_format($invoice->balance, 0, ',', ' ') }} FCFA
        </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
        <div class="notes">
            <strong>Notes :</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    {{-- Payment Info --}}
    @if($invoice->terms)
        <div class="payment-info">
            <strong>Conditions de paiement :</strong><br>
            {{ $invoice->terms }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                {{ $company->name }} - Document généré le {{ now()->format('d/m/Y à H:i') }}
            </div>
            <div class="footer-right">
                Facture N° {{ $invoice->invoice_number }} - Page 1/1
            </div>
        </div>
    </div>
</body>
</html>
