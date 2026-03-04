<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Devis {{ $quote->quote_number }}</title>
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
            border-bottom: 3px solid #2196F3;
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
            color: #2196F3;
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
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
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
        .validity-box {
            margin-top: 15px;
            padding: 10px;
            background-color: #fff8e1;
            border: 1px solid #ffcc02;
            border-radius: 4px;
        }
        .validity-box.expired {
            background-color: #ffebee;
            border-color: #f44336;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.items thead {
            background-color: #1976D2;
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
            border-top: 2px solid #1976D2;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 14pt;
            font-weight: bold;
            color: #2196F3;
        }
        .amount-words {
            margin-top: 25px;
            padding: 12px;
            background-color: #f5f5f5;
            border-left: 4px solid #2196F3;
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
        .terms {
            margin-top: 20px;
            padding: 12px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 45%;
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .signature-box.right {
            margin-left: 10%;
        }
        .signature-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 50px;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 8pt;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: bold;
            margin-top: 10px;
        }
        .status-accepted {
            background-color: #4caf50;
            color: white;
        }
        .status-rejected {
            background-color: #f44336;
            color: white;
        }
        .status-converted {
            background-color: #9c27b0;
            color: white;
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
    @if($quote->status === 'draft')
        <div class="watermark">BROUILLON</div>
    @elseif($quote->status === 'expired' || ($quote->valid_until && $quote->valid_until->isPast()))
        <div class="watermark">EXPIRÉ</div>
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
            <div class="doc-title">DEVIS</div>
            <div class="doc-number">N° {{ $quote->quote_number }}</div>
            @if($quote->status === 'accepted')
                <div class="status-badge status-accepted">✓ ACCEPTÉ</div>
            @elseif($quote->status === 'rejected')
                <div class="status-badge status-rejected">✗ REFUSÉ</div>
            @elseif($quote->status === 'converted')
                <div class="status-badge status-converted">→ FACTURÉ</div>
            @endif
        </div>
    </div>

    {{-- Info Section --}}
    <div class="info-section">
        <div class="info-box client">
            <div class="info-label">Destinataire</div>
            <div class="info-value highlight">{{ $quote->client_name }}</div>
            @if($quote->client_address)
                <div class="info-value">{{ $quote->client_address }}</div>
            @endif
            @if($quote->client_email)
                <div class="info-value">{{ $quote->client_email }}</div>
            @endif
            @if($quote->client_phone)
                <div class="info-value">{{ $quote->client_phone }}</div>
            @endif
        </div>
        <div class="info-box" style="text-align: right;">
            <div class="info-label">Date du devis</div>
            <div class="info-value highlight">{{ $quote->quote_date->format('d/m/Y') }}</div>
            
            @if($quote->valid_until)
            <div class="validity-box {{ $quote->valid_until->isPast() ? 'expired' : '' }}">
                <div class="info-label">Valide jusqu'au</div>
                <div class="info-value highlight">{{ $quote->valid_until->format('d/m/Y') }}</div>
                @if($quote->valid_until->isPast())
                    <small style="color: #f44336;">⚠ Ce devis a expiré</small>
                @elseif($quote->valid_until->diffInDays(now()) <= 7)
                    <small style="color: #ff9800;">Expire dans {{ $quote->valid_until->diffInDays(now()) }} jour(s)</small>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    @php $hasLineDisc = $quote->items->where('discount_amount', '>', 0)->isNotEmpty(); @endphp
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: {{ $hasLineDisc ? '33%' : '40%' }};">Description</th>
                <th class="center" style="width: 10%;">Type</th>
                <th class="center" style="width: 8%;">Qté</th>
                <th class="right" style="width: 12%;">P.U. HT</th>
                @if($hasLineDisc)
                <th class="right" style="width: 14%;">Remise</th>
                @endif
                <th class="right" style="width: 18%;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $index => $item)
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
                @if($hasLineDisc)
                <td class="right" style="color:#c62828;">
                    @if($item->discount_amount > 0)
                        −{{ number_format($item->discount_amount, 0, ',', ' ') }}
                    @else
                        —
                    @endif
                </td>
                @endif
                <td class="right"><strong>{{ number_format($item->total, 0, ',', ' ') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Sous-total HT</div>
            <div class="totals-value">{{ number_format($quote->subtotal, 0, ',', ' ') }} FCFA</div>
        </div>
        @if($quote->discount_amount > 0)
        <div class="totals-row">
            <div class="totals-label">
                Remise
                @if($quote->promo_code) ({{ $quote->promo_code }})@endif
                @if($quote->discount_type === 'percent') ({{ $quote->discount_value }}%)@endif
            </div>
            <div class="totals-value" style="color:#c62828;">−{{ number_format($quote->discount_amount, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Net HT</div>
            <div class="totals-value">{{ number_format($quote->subtotal - $quote->discount_amount, 0, ',', ' ') }} FCFA</div>
        </div>
        @endif
        @foreach($quote->taxes as $tax)
        <div class="totals-row">
            <div class="totals-label">{{ $tax->tax_name }} ({{ $tax->tax_rate }}%)</div>
            <div class="totals-value">{{ number_format($tax->tax_amount, 0, ',', ' ') }} FCFA</div>
        </div>
        @endforeach
        <div class="totals-row grand">
            <div class="totals-label">Total TTC</div>
            <div class="totals-value">{{ number_format($quote->total_amount, 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    {{-- Amount in Words --}}
    <div class="amount-words">
        <strong>Montant total :</strong><br>
        {{ $quote->total_in_words ?? number_to_words($quote->total_amount) }} francs CFA
    </div>

    {{-- Notes --}}
    @if($quote->notes)
        <div class="notes">
            <strong>Notes :</strong><br>
            {{ $quote->notes }}
        </div>
    @endif

    {{-- Terms --}}
    <div class="terms">
        <strong>Conditions :</strong><br>
        {{ $quote->terms ?? 'Devis valable 30 jours à compter de sa date d\'émission. Prix exprimés en Francs CFA, toutes taxes comprises.' }}
    </div>

    {{-- Signature Section --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Signature du fournisseur</div>
            <div class="signature-line">{{ $company->name }}</div>
        </div>
        <div class="signature-box right">
            <div class="signature-label">Bon pour accord - Signature du client</div>
            <div class="signature-line">Date :</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                {{ $company->name }} - Document généré le {{ now()->format('d/m/Y à H:i') }}
            </div>
            <div class="footer-right">
                Devis N° {{ $quote->quote_number }} - Page 1/1
            </div>
        </div>
    </div>
</body>
</html>
