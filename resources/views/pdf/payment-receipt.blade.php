<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reçu {{ $payment->payment_number }}</title>
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

        .doc-title { font-size: 28pt; font-weight: bold; color: #9333EA; margin-bottom: 3px; }
        .doc-number { font-size: 11pt; color: #666; }

        .info-section { display: table; width: 100%; margin-bottom: 15px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; padding: 10px; line-height: 1.3; }
        .info-box.client { background-color: #f8f9fa; border-left: 4px solid #9333EA; }
        .info-label { font-size: 8pt; color: #999; text-transform: uppercase; margin-bottom: 2px; }
        .info-value { font-size: 10pt; margin-bottom: 2px; }
        .info-value.highlight { font-weight: bold; font-size: 11pt; }

        .amount-box {
            background-color: #7E22CE;
            color: white;
            padding: 18px;
            text-align: center;
            margin: 18px 0;
        }
        .amount-box .label {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.85;
        }
        .amount-box .amount {
            font-size: 28pt;
            font-weight: bold;
            margin-top: 6px;
        }

        table.items { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.items thead { background-color: #7E22CE; color: white; }
        table.items th { padding: 10px 8px; text-align: left; font-size: 9pt; font-weight: bold; }
        table.items td { padding: 8px; border-bottom: 1px solid #e0e0e0; font-size: 9pt; }
        table.items td.right { text-align: right; }
        table.items tbody tr:nth-child(even) { background-color: #fafafa; }

        .totals { width: 50%; margin-left: auto; margin-top: 15px; }
        .totals-row { display: table; width: 100%; padding: 5px 0; }
        .totals-label, .totals-value { display: table-cell; }
        .totals-label { text-align: right; padding-right: 15px; color: #666; }
        .totals-value { text-align: right; width: 140px; font-weight: bold; }
        .totals-row.grand { border-top: 2px solid #7E22CE; margin-top: 8px; padding-top: 8px; font-size: 12pt; font-weight: bold; color: #9333EA; white-space: nowrap; }

        .notes { margin-top: 15px; padding: 12px; background-color: #fffbf0; border: 1px solid #ffe0b2; border-left: 3px solid #9333EA; font-size: 9pt; }

        .signature-section { display: table; width: 100%; margin-top: 35px; }
        .signature-box { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
        .signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 4px; font-size: 9pt; color: #666; }

        .badge-paid {
            display: inline-block;
            padding: 4px 12px;
            background-color: #16a34a;
            color: white;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        @include('pdf.partials._dynamic-styles')
    </style>
</head>
<body>
    @if(isset($style) && $style->background_image)
        <div class="bg-overlay"></div>
    @endif

    {{-- EN-TETE REPETE --}}
    @include('pdf.partials._block-header')

    {{-- PIED DE PAGE REPETE --}}
    @include('pdf.partials._block-footer', ['footerRight' => 'Reçu N ' . $payment->payment_number])

    {{-- ===== CORPS ===== --}}

    {{-- Bloc titre + dates + client --}}
    <div class="info-section">
        <div class="info-box">
            <div class="doc-title">REÇU</div>
            <div class="doc-number">N° {{ $payment->payment_number }}</div>
            <div class="info-value" style="margin-top:6px">
                <span class="info-label">Date :</span> {{ $payment->payment_date->format('d/m/Y') }}
            </div>
            <div class="info-value">
                <span class="info-label">Facture :</span> {{ $payment->invoice->invoice_number }}
            </div>
            @if($payment->invoice->payment_status === 'paid')
                <div style="margin-top: 6px;"><span class="badge-paid">Facture payée</span></div>
            @endif
        </div>
        <div class="info-box client">
            <div class="info-label">Reçu de</div>
            <div class="info-value highlight">{{ $payment->invoice->client_name }}</div>
            @if($payment->invoice->client_email)
                <div class="info-value">{{ $payment->invoice->client_email }}</div>
            @endif
            @if($payment->invoice->client_phone)
                <div class="info-value">{{ $payment->invoice->client_phone }}</div>
            @endif
            @if($payment->invoice->client_address)
                <div class="info-value">{{ $payment->invoice->client_address }}</div>
            @endif
        </div>
    </div>

    {{-- Montant reçu (mis en évidence) --}}
    <div class="amount-box">
        <div class="label">Montant reçu</div>
        <div class="amount">{{ format_currency((float) $payment->amount) }}</div>
    </div>

    {{-- Détails du paiement (table.items pour bénéficier du dynamic-styles) --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:35%">Information</th>
                <th>Détail</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Date du paiement</strong></td>
                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Mode de paiement</strong></td>
                <td>{{ $payment->method_label ?? $payment->payment_method?->label() }}</td>
            </tr>
            @if($payment->reference)
            <tr>
                <td><strong>Référence</strong></td>
                <td>{{ $payment->reference }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Statut</strong></td>
                <td>
                    @if($payment->is_confirmed)
                        Confirmé{{ $payment->confirmed_at ? ' le ' . $payment->confirmed_at->format('d/m/Y à H:i') : '' }}
                    @else
                        En attente de confirmation
                    @endif
                </td>
            </tr>
            @if($payment->user)
            <tr>
                <td><strong>Encaissé par</strong></td>
                <td>{{ $payment->user->name }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Récapitulatif facture --}}
    <div class="totals">
        <div class="totals-row">
            <div class="totals-label">Total facture</div>
            <div class="totals-value">{{ format_currency((float) $payment->invoice->total_amount) }}</div>
        </div>
        <div class="totals-row">
            <div class="totals-label">Total payé</div>
            <div class="totals-value">{{ format_currency((float) $payment->invoice->paid_amount) }}</div>
        </div>
        <div class="totals-row grand">
            <div class="totals-label">Solde restant</div>
            <div class="totals-value">{{ format_currency((float) $payment->invoice->balance) }}</div>
        </div>
    </div>

    @if($payment->notes)
        <div class="notes">
            <strong>Notes :</strong> {{ $payment->notes }}
        </div>
    @endif

    {{-- Signatures --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Signature du client</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Signature de l'encaisseur</div>
        </div>
    </div>
</body>
</html>
