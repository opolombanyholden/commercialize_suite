<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bon de livraison {{ $delivery->delivery_number }}</title>
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
            border-bottom: 3px solid #4CAF50;
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
            font-size: 24pt;
            font-weight: bold;
            color: #4CAF50;
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
            width: 33%;
            vertical-align: top;
            padding: 10px;
        }
        .info-box.client {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
        }
        .info-box.delivery {
            background-color: #fff3e0;
            border-left: 4px solid #FF9800;
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
        .reference-box {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px dashed #999;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.items thead {
            background-color: #388E3C;
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
            padding: 10px 8px;
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
        .check-col {
            width: 30px;
            text-align: center;
        }
        .checkbox {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #666;
            border-radius: 3px;
        }
        .summary-section {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        .summary-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        .summary-box h4 {
            font-size: 10pt;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #4CAF50;
        }
        .summary-item {
            display: table;
            width: 100%;
            padding: 3px 0;
        }
        .summary-label, .summary-value {
            display: table-cell;
        }
        .summary-label {
            color: #666;
        }
        .summary-value {
            text-align: right;
            font-weight: bold;
        }
        .notes {
            margin-top: 20px;
            padding: 12px;
            background-color: #fffbf0;
            border: 1px solid #ffe0b2;
            font-size: 9pt;
        }
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 48%;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .signature-box.right {
            margin-left: 4%;
        }
        .signature-header {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .signature-field {
            margin-bottom: 15px;
        }
        .signature-field label {
            font-size: 8pt;
            color: #666;
            display: block;
            margin-bottom: 3px;
        }
        .signature-field .line {
            border-bottom: 1px solid #333;
            height: 25px;
        }
        .signature-area {
            height: 60px;
            border: 1px dashed #ccc;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 8pt;
        }
        .status-delivered {
            margin-top: 20px;
            text-align: center;
            padding: 15px;
            background-color: #4caf50;
            color: white;
            font-size: 14pt;
            font-weight: bold;
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
            font-size: 70pt;
            color: rgba(0, 0, 0, 0.04);
            transform: rotate(-45deg);
            z-index: -1;
        }
    </style>
</head>
<body>
    @if($delivery->status === 'pending')
        <div class="watermark">EN ATTENTE</div>
    @elseif($delivery->status === 'cancelled')
        <div class="watermark">ANNULÉ</div>
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
                @if($company->phone)Tél: {{ $company->phone }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">BON DE LIVRAISON</div>
            <div class="doc-number">N° {{ $delivery->delivery_number }}</div>
        </div>
    </div>

    {{-- Reference Box --}}
    @if($delivery->invoice)
    <div class="reference-box">
        <strong>Référence facture :</strong> {{ $delivery->invoice->invoice_number }}
        <span style="margin-left: 30px;"><strong>Date facture :</strong> {{ $delivery->invoice->invoice_date->format('d/m/Y') }}</span>
    </div>
    @endif

    {{-- Info Section --}}
    <div class="info-section">
        <div class="info-box client">
            <div class="info-label">Destinataire</div>
            <div class="info-value highlight">{{ $delivery->client_name }}</div>
            @if($delivery->delivery_address)
                <div class="info-value">{{ $delivery->delivery_address }}</div>
            @endif
            @if($delivery->client_phone)
                <div class="info-value">Tél: {{ $delivery->client_phone }}</div>
            @endif
        </div>
        <div class="info-box delivery">
            <div class="info-label">Livraison prévue</div>
            <div class="info-value highlight">{{ $delivery->scheduled_date ? $delivery->scheduled_date->format('d/m/Y') : '-' }}</div>
            @if($delivery->scheduled_time)
                <div class="info-value">Heure: {{ $delivery->scheduled_time }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="info-label">Livreur</div>
            <div class="info-value highlight">{{ $delivery->driver_name ?? '-' }}</div>
            @if($delivery->vehicle_info)
                <div class="info-value">{{ $delivery->vehicle_info }}</div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Description</th>
                <th class="center" style="width: 15%;">Qté commandée</th>
                <th class="center" style="width: 15%;">Qté livrée</th>
                <th class="center check-col" style="width: 10%;">Contrôle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($delivery->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->description }}</strong>
                    @if($item->sku)
                        <br><small style="color: #999;">Réf: {{ $item->sku }}</small>
                    @endif
                </td>
                <td class="center">{{ number_format($item->quantity_ordered, 0, ',', ' ') }}</td>
                <td class="center"><strong>{{ number_format($item->quantity_delivered, 0, ',', ' ') }}</strong></td>
                <td class="center"><span class="checkbox"></span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary Section --}}
    <div class="summary-section">
        <div class="summary-box">
            <h4>Récapitulatif</h4>
            <div class="summary-item">
                <div class="summary-label">Nombre d'articles</div>
                <div class="summary-value">{{ $delivery->items->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Quantité totale</div>
                <div class="summary-value">{{ $delivery->items->sum('quantity_delivered') }}</div>
            </div>
            @if($delivery->total_weight)
            <div class="summary-item">
                <div class="summary-label">Poids total</div>
                <div class="summary-value">{{ $delivery->total_weight }} kg</div>
            </div>
            @endif
            @if($delivery->packages_count)
            <div class="summary-item">
                <div class="summary-label">Nombre de colis</div>
                <div class="summary-value">{{ $delivery->packages_count }}</div>
            </div>
            @endif
        </div>
        <div class="summary-box">
            <h4>Observations à la livraison</h4>
            <div style="height: 80px; border: 1px solid #ddd; padding: 8px;">
                <small style="color: #999;">À remplir par le livreur</small>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($delivery->notes)
        <div class="notes">
            <strong>Instructions de livraison :</strong><br>
            {{ $delivery->notes }}
        </div>
    @endif

    {{-- Signature Section --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-header">Expéditeur</div>
            <div class="signature-field">
                <label>Nom et fonction</label>
                <div class="line"></div>
            </div>
            <div class="signature-field">
                <label>Date et heure de départ</label>
                <div class="line"></div>
            </div>
            <div class="signature-area">Signature et cachet</div>
        </div>
        <div class="signature-box right">
            <div class="signature-header">Réception client</div>
            <div class="signature-field">
                <label>Nom du réceptionnaire</label>
                <div class="line"></div>
            </div>
            <div class="signature-field">
                <label>Date et heure de réception</label>
                <div class="line"></div>
            </div>
            <div class="signature-area">Signature "Bon pour réception"</div>
        </div>
    </div>

    {{-- Status Delivered --}}
    @if($delivery->status === 'delivered')
        <div class="status-delivered">
            ✓ LIVRÉ LE {{ $delivery->delivered_at ? $delivery->delivered_at->format('d/m/Y à H:i') : '-' }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                {{ $company->name }} - Document généré le {{ now()->format('d/m/Y à H:i') }}
            </div>
            <div class="footer-right">
                BL N° {{ $delivery->delivery_number }} - Page 1/1
            </div>
        </div>
    </div>
</body>
</html>
