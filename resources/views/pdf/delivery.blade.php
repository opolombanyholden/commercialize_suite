<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bon de livraison {{ $delivery->delivery_number }}</title>
    <style>
        @page { margin: 1.5cm; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #333; }

        .header { display: table; width: 100%; margin-bottom: 25px; border-bottom: 3px solid #2563EB; padding-bottom: 15px; }
        .header-left, .header-right { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { text-align: right; }
        .logo { max-width: 150px; max-height: 60px; }
        .company-info { margin-top: 8px; font-size: 9pt; color: #666; }
        .doc-title { font-size: 26pt; font-weight: bold; color: #2563EB; margin-bottom: 4px; }
        .doc-number { font-size: 11pt; color: #666; }
        .doc-status { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 9pt; font-weight: bold; margin-top: 6px; }
        .status-pending    { background: #FEF3C7; color: #92400E; }
        .status-in_transit { background: #DBEAFE; color: #1E40AF; }
        .status-delivered  { background: #D1FAE5; color: #065F46; }
        .status-cancelled  { background: #F3F4F6; color: #374151; }

        .info-section { display: table; width: 100%; margin-bottom: 20px; }
        .info-box { display: table-cell; width: 48%; vertical-align: top; border: 1px solid #E5E7EB; border-radius: 4px; padding: 12px; }
        .info-box + .info-box { margin-left: 4%; }
        .info-label { font-size: 8pt; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; font-weight: bold; }
        .info-value { font-size: 10pt; }
        .info-value strong { font-size: 11pt; }

        .dates-section { display: table; width: 100%; margin-bottom: 20px; }
        .date-box { display: table-cell; width: 33%; vertical-align: top; padding: 10px; border: 1px solid #E5E7EB; border-radius: 4px; text-align: center; }
        .date-box + .date-box { margin-left: 2%; }
        .date-box .label { font-size: 8pt; color: #6B7280; text-transform: uppercase; margin-bottom: 4px; }
        .date-box .value { font-size: 11pt; font-weight: bold; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead th { background: #2563EB; color: #fff; padding: 8px 10px; font-size: 9pt; text-align: left; }
        table.items thead th.center { text-align: center; }
        table.items tbody td { padding: 8px 10px; border-bottom: 1px solid #E5E7EB; font-size: 10pt; vertical-align: top; }
        table.items tbody tr:nth-child(even) td { background: #F9FAFB; }
        table.items tbody td.center { text-align: center; }
        table.items tfoot td { padding: 8px 10px; background: #F3F4F6; font-weight: bold; border-top: 2px solid #2563EB; }

        .signature-section { display: table; width: 100%; margin-top: 30px; }
        .sig-box { display: table-cell; width: 48%; vertical-align: top; border: 1px solid #D1D5DB; border-radius: 4px; padding: 12px; }
        .sig-box + .sig-box { margin-left: 4%; }
        .sig-label { font-size: 9pt; color: #6B7280; margin-bottom: 8px; }
        .sig-area { height: 70px; border-bottom: 1px solid #9CA3AF; }
        .sig-image { max-height: 70px; max-width: 100%; }
        .sig-name { font-size: 9pt; color: #374151; margin-top: 6px; text-align: center; }

        .notes-section { background: #FFF7ED; border-left: 4px solid #F97316; padding: 10px 14px; margin-top: 16px; font-size: 9pt; }
        .footer { text-align: center; font-size: 8pt; color: #9CA3AF; margin-top: 30px; padding-top: 10px; border-top: 1px solid #E5E7EB; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if($company->logo_path)
                <img src="{{ public_path('storage/' . $company->logo_path) }}" class="logo" alt="Logo">
            @endif
            <div class="company-info">
                <strong>{{ $company->name }}</strong><br>
                @if($company->address){{ $company->address }}<br>@endif
                @if($company->phone)Tél : {{ $company->phone }}<br>@endif
                @if($company->email){{ $company->email }}<br>@endif
                @if($company->tax_number)NIF : {{ $company->tax_number }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">BON DE LIVRAISON</div>
            <div class="doc-number">{{ $delivery->delivery_number }}</div>
            <div>
                <span class="doc-status status-{{ $delivery->status }}">{{ $delivery->status_label }}</span>
            </div>
        </div>
    </div>

    {{-- Info client + livraison --}}
    <div class="info-section">
        <div class="info-box">
            <div class="info-label">Destinataire</div>
            <div class="info-value">
                <strong>{{ $delivery->client_name }}</strong><br>
                @if($delivery->client_phone)Tél : {{ $delivery->client_phone }}<br>@endif
                @if($delivery->client_email){{ $delivery->client_email }}<br>@endif
                @if($delivery->delivery_address){{ $delivery->delivery_address }}@endif
            </div>
        </div>
        <div class="info-box" style="margin-left:4%;">
            <div class="info-label">Informations livraison</div>
            <div class="info-value">
                @if($delivery->invoice)
                    <strong>Facture :</strong> {{ $delivery->invoice->invoice_number }}<br>
                @endif
                @if($delivery->livreur)
                    <strong>Livreur :</strong> {{ $delivery->livreur }}<br>
                @endif
                <strong>Date prévue :</strong> {{ $delivery->planned_date->format('d/m/Y') }}<br>
                @if($delivery->delivered_date)
                    <strong>Date effective :</strong> {{ $delivery->delivered_date->format('d/m/Y') }}
                @endif
            </div>
        </div>
    </div>

    {{-- Articles --}}
    @php $recap = $delivery->getDeliveryRecap(); $hasInvoice = $delivery->invoice_id && !empty($recap); @endphp
    <table class="items">
        <thead>
            <tr>
                <th style="width:25px;">#</th>
                <th>Description</th>
                @if($hasInvoice)
                    <th class="center" style="width:75px;">Commandé</th>
                    <th class="center" style="width:75px;">Déjà livré</th>
                @endif
                <th class="center" style="width:80px;">Cette livraison</th>
                @if($hasInvoice)
                    <th class="center" style="width:75px;">Reste</th>
                @endif
                <th style="width:55px;">Unité</th>
            </tr>
        </thead>
        <tbody>
            @if($hasInvoice)
                @foreach($recap as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td class="center" style="color:#9CA3AF;">{{ $row['ordered'] !== null ? number_format($row['ordered'], 2) : '—' }}</td>
                    <td class="center" style="color:#059669;">{{ number_format($row['already_delivered'], 2) }}</td>
                    <td class="center"><strong>{{ number_format($row['this_delivery'], 2) }}</strong></td>
                    <td class="center" style="color:{{ ($row['remaining'] ?? 0) > 0 ? '#D97706' : '#059669' }}; font-weight:bold;">
                        {{ $row['remaining'] !== null ? number_format($row['remaining'], 2) : '—' }}
                    </td>
                    <td>{{ $row['unit'] ?? '' }}</td>
                </tr>
                @endforeach
            @else
                @foreach($delivery->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="center"><strong>{{ number_format($item->quantity, 2) }}</strong></td>
                    <td>{{ $item->unit ?? '' }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ $hasInvoice ? 2 : 2 }}" style="text-align:right;">Totaux</td>
                @if($hasInvoice)
                    <td class="center" style="color:#9CA3AF;">{{ number_format(collect($recap)->sum(fn($r) => $r['ordered'] ?? 0), 2) }}</td>
                    <td class="center" style="color:#059669;">{{ number_format(collect($recap)->sum('already_delivered'), 2) }}</td>
                @endif
                <td class="center">{{ number_format($hasInvoice ? collect($recap)->sum('this_delivery') : $delivery->items->sum('quantity'), 2) }}</td>
                @if($hasInvoice)
                    <td class="center" style="color:{{ collect($recap)->sum(fn($r) => $r['remaining'] ?? 0) > 0 ? '#D97706' : '#059669' }};">
                        {{ number_format(collect($recap)->sum(fn($r) => $r['remaining'] ?? 0), 2) }}
                    </td>
                @endif
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Notes --}}
    @if($delivery->notes)
    <div class="notes-section">
        <strong>Notes :</strong> {{ $delivery->notes }}
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signature-section">
        <div class="sig-box">
            <div class="sig-label">Signature du livreur</div>
            <div class="sig-area"></div>
            <div class="sig-name">{{ $delivery->livreur ?? '____________________' }}</div>
        </div>
        <div class="sig-box" style="margin-left:4%;">
            <div class="sig-label">Signature du destinataire</div>
            @if($delivery->signature)
                <img src="{{ $delivery->signature }}" class="sig-image" alt="Signature">
            @else
                <div class="sig-area"></div>
                <div class="sig-name">{{ $delivery->client_name }}</div>
            @endif
        </div>
    </div>

    <div class="footer">
        {{ $company->name }} — {{ $delivery->delivery_number }} — Imprimé le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
