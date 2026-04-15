<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $invoice->invoice_number }}</title>
    @php
        $hH = isset($style) ? ($style->header_height_cm ?? 3.0) : 3.0;
        $fH = isset($style) ? ($style->footer_height_cm ?? 2.5) : 2.5;
        $mT = $hH + 0.2;
        $mB = $fH + 0.2;
    @endphp
    <style>
        @page {
            margin-top: {{ $mT }}cm;
            margin-bottom: {{ $mB }}cm;
            margin-left: 1.5cm;
            margin-right: 1.5cm;
            size: A4 portrait;
        }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #333; margin: 0; padding: 0; }

        .page-header { position: fixed; top: -{{ $mT }}cm; left: -1.5cm; right: -1.5cm; height: {{ $hH }}cm; padding: 10px 1.5cm 8px 1.5cm; }
        .page-footer { position: fixed; bottom: -{{ $mB }}cm; left: -1.5cm; right: -1.5cm; height: {{ $fH }}cm; border-top: 1px solid #ddd; padding: 6px 1.5cm 0 1.5cm; font-size: 8pt; color: #999; }
        .logo { max-width: 150px; max-height: 60px; }
        .company-info { font-size: 8pt; color: #666; margin-top: 4px; }

        .doc-title { font-size: 28pt; font-weight: bold; color: #FF6B35; margin-bottom: 3px; }
        .doc-number { font-size: 11pt; color: #666; }
        .info-section { display: table; width: 100%; margin-bottom: 15px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; padding: 10px; line-height: 1.3; }
        .info-box.client { background-color: #f8f9fa; border-left: 4px solid #FF6B35; }
        .info-label { font-size: 8pt; color: #999; text-transform: uppercase; margin-bottom: 2px; }
        .info-value { font-size: 10pt; margin-bottom: 2px; }
        .info-value.highlight { font-weight: bold; font-size: 11pt; }

        table.items { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.items thead { background-color: #004E89; color: white; }
        table.items th { padding: 10px 8px; text-align: left; font-size: 9pt; font-weight: bold; }
        table.items th.right { text-align: right; }
        table.items th.center { text-align: center; }
        table.items td { padding: 8px; border-bottom: 1px solid #e0e0e0; font-size: 9pt; }
        table.items td.right { text-align: right; }
        table.items td.center { text-align: center; }
        table.items tbody tr:nth-child(even) { background-color: #fafafa; }

        .totals { width: 45%; margin-left: auto; margin-top: 15px; }
        .totals-row { display: table; width: 100%; padding: 5px 0; }
        .totals-label, .totals-value { display: table-cell; }
        .totals-label { text-align: right; padding-right: 15px; color: #666; }
        .totals-value { text-align: right; width: 120px; }
        .totals-row.grand { border-top: 2px solid #004E89; margin-top: 8px; padding-top: 8px; font-size: 12pt; font-weight: bold; color: #FF6B35; white-space: nowrap; }

        .amount-words { margin-top: 15px; font-size: 9pt; }
        .notes { margin-top: 15px; padding: 12px; background-color: #fffbf0; border: 1px solid #ffe0b2; font-size: 9pt; }
        .watermark { position: fixed; top: 45%; left: 25%; font-size: 80pt; color: rgba(0,0,0,0.04); transform: rotate(-45deg); z-index: -1; }

        @include('pdf.partials._dynamic-styles')
    </style>
</head>
<body>
    @if(isset($style) && $style->background_image)
        <div class="bg-overlay"></div>
    @endif
    @if($invoice->status === 'draft')
        <div class="watermark">BROUILLON</div>
    @elseif($invoice->status === 'cancelled')
        <div class="watermark">ANNULEE</div>
    @endif

    {{-- EN-TETE REPETE --}}
    @include('pdf.partials._block-header')

    {{-- PIED DE PAGE REPETE --}}
    @include('pdf.partials._block-footer', ['footerRight' => 'Facture N ' . $invoice->invoice_number])

    {{-- ===== CORPS ===== --}}

    {{-- Bloc titre + dates + client --}}
    <div class="info-section">
        <div class="info-box client">
            <div class="info-label">Facture a</div>
            <div class="info-value highlight">{{ $invoice->client_name }}</div>
            @if($invoice->client_address)<div class="info-value">{{ $invoice->client_address }}</div>@endif
            @if($invoice->client_email)<div class="info-value">{{ $invoice->client_email }}</div>@endif
            @if($invoice->client_phone)<div class="info-value">{{ $invoice->client_phone }}</div>@endif
        </div>
        <div class="info-box" style="text-align: right; line-height: 1.3;">
            <div class="doc-title">FACTURE</div>
            <div class="doc-number">N {{ $invoice->invoice_number }}</div>

            <div style="display: table; width: 100%; margin-top: 10px;">
                <div style="display: table-row;">
                    <div style="display: table-cell; text-align: right; padding-right: 10px;">
                        <div class="info-label">Date de facturation</div>
                        <div class="info-value highlight">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    </div>
                    <div style="display: table-cell; text-align: right;">
                        <div class="info-label">Date d'echeance</div>
                        <div class="info-value highlight" style="{{ $invoice->is_overdue ? 'color:#d32f2f;' : '' }}">
                            {{ $invoice->due_date->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pdf.partials._subject-block', ['document' => $invoice])

    @php $hasLineDisc = $invoice->items->where('discount_amount', '>', 0)->isNotEmpty(); @endphp
    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:{{ $hasLineDisc ? '43%' : '55%' }};">Description</th>
                <th class="center" style="width:10%;">Qte</th>
                <th class="right" style="width:12%;">P.U. HT</th>
                @if($hasLineDisc)<th class="right" style="width:12%;">Remise</th>@endif
                <th class="right" style="width:18%;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="center">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td class="right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                @if($hasLineDisc)
                <td class="right" style="color:#c62828;">
                    @if($item->discount_amount > 0)−{{ number_format($item->discount_amount, 0, ',', ' ') }}@else —@endif
                </td>
                @endif
                <td class="right"><strong>{{ number_format($item->total, 0, ',', ' ') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Sous-total HT</div>
            <div class="totals-value">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</div>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="totals-row">
            <div class="totals-label">Remise @if($invoice->promo_code)({{ $invoice->promo_code }})@endif @if($invoice->discount_type === 'percent')({{ $invoice->discount_value }}%)@endif</div>
            <div class="totals-value" style="color:#c62828;">−{{ number_format($invoice->discount_amount, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Net HT</div>
            <div class="totals-value">{{ number_format($invoice->subtotal - $invoice->discount_amount, 0, ',', ' ') }} FCFA</div>
        </div>
        @endif
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

    {{-- Montant en lettres --}}
    @php
        $netHT = $invoice->subtotal - ($invoice->discount_amount ?? 0);
    @endphp
    <div class="amount-words">
        Arrete la presente facture a la somme de <strong>{{ number_to_words($netHT) }} francs CFA</strong> Hors Taxes et de <strong>{{ $invoice->total_in_words ?? number_to_words($invoice->total_amount) }} francs CFA</strong> Toutes Taxes Comprises.
    </div>

    @if($invoice->notes)
        <div class="notes"><strong>Notes :</strong><br>{{ $invoice->notes }}</div>
    @endif

    {{-- Conditions de vente --}}
    @include('pdf.partials._sales-conditions', ['document' => $invoice])

    {{-- Signatures --}}
    @include('pdf.partials._signature-section', [
        'company' => $company,
        'withSignature' => $withSignature ?? false,
        'clientLabel' => $invoice->client->company_name ?? $invoice->client_name ?? '........................',
    ])

    {{-- Code de livraison --}}
    @if($invoice->hasDeliveryPin())
    @php
        $firstDn = $invoice->deliveryNotes->first();
        $qrUrl = $firstDn ? route('delivery.public', $firstDn->public_token) : url('/');
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->margin(1)->generate($qrUrl);
    @endphp
    <div style="display:table; width:100%; margin-top:15px; border:1px solid #FFD700; border-radius:4px; background:#FFFBEB; padding:12px;">
        <div style="display:table-cell; vertical-align:middle; width:115px; text-align:center;">{!! $qrSvg !!}</div>
        <div style="display:table-cell; vertical-align:middle; padding-left:16px;">
            <div style="font-size:8pt; color:#92400E; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px; font-weight:bold;">Code de livraison</div>
            <div style="font-size:22pt; font-weight:bold; color:#1E293B; letter-spacing:0.2em; font-family:'Courier New', monospace;">{{ $invoice->delivery_pin }}</div>
            <div style="font-size:8pt; color:#92400E; margin-top:6px;">A communiquer au livreur lors de la reception.</div>
        </div>
    </div>
    @endif
</body>
</html>
