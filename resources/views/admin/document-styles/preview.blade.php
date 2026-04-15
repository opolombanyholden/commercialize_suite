<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Apercu - {{ $typeName }}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; background: #eee; }
        .toolbar { text-align: center; padding: 15px; background: #333; color: #fff; margin-bottom: 20px; border-radius: 8px; }
        .toolbar a { color: #fff; margin: 0 10px; text-decoration: none; }
        .preview-frame {
            background: {{ $style->background_color ?? '#FFFFFF' }};
            padding: 40px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.15);
            position: relative;
            @if($style->background_image)
            background-image: url('{{ Storage::url($style->background_image) }}');
            background-size: cover;
            background-repeat: no-repeat;
            @endif
        }

        /* En-tete */
        .page-header {
            border-bottom: 3px solid {{ $style->primary_color }};
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .logo-img { max-width: 120px; max-height: 45px; }
        .company-info { font-size: 9pt; color: #666; margin-top: 6px; }

        /* Titre document */
        .doc-title { font-size: 28pt; font-weight: bold; color: {{ $style->primary_color }}; margin-bottom: 3px; }
        .doc-number { font-size: 11pt; color: #666; }

        /* Info section */
        .info-section { display: table; width: 100%; margin: 15px 0; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; padding: 10px; }
        .info-box.client { background-color: #f8f9fa; border-left: 4px solid {{ $style->primary_color }}; }
        .info-label { font-size: 8pt; color: #999; text-transform: uppercase; margin-bottom: 3px; }
        .info-value { font-size: 10pt; margin-bottom: 5px; }
        .info-value.highlight { font-weight: bold; }

        /* Tableau */
        table.items { width: 100%; border-collapse: collapse; margin: 15px 0; font-family: '{{ $style->table_font_family ?? 'DejaVu Sans' }}', sans-serif; }
        table.items thead tr { background-color: {{ $style->table_header_color }}; color: white; }
        table.items th { padding: 10px 8px; text-align: left; font-size: 9pt; }
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9pt;
            color: {{ $style->table_text_color ?? '#333' }};
            font-weight: {{ $style->font_weight }};
            font-style: {{ $style->font_style_css }};
        }
        table.items tbody tr:nth-child(odd) { background-color: {{ $style->table_odd_row_color ?? '#FFFFFF' }}; }
        table.items tbody tr:nth-child(even) { background-color: {{ $style->table_even_row_color ?? '#FAFAFA' }}; }

        /* Pied de page */
        .page-footer { border-top: 1px solid #ddd; padding-top: 10px; font-size: 8pt; color: #999; margin-top: 30px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong>Apercu du style - {{ $typeName }}</strong> |
        <a href="{{ route('settings.documents.index', ['tab' => $documentType]) }}">Retour aux parametres</a>
    </div>

    <div class="preview-frame">

        {{-- EN-TETE : Bloc Logo + Bloc Infos entreprise (positions independantes) --}}
        @php
            $logoPos = $style->logo_position ?? 'left';
            $infoPos = $style->company_info_position ?? 'left';

            $slots = ['left' => [], 'center' => [], 'right' => []];
            if ($company->logo_path) {
                $slots[$logoPos][] = 'logo';
            }
            $slots[$infoPos][] = 'info';
            $usedCols = collect($slots)->filter(fn($s) => count($s) > 0)->count();
        @endphp
        <div class="page-header">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    @foreach(['left', 'center', 'right'] as $pos)
                        @if(count($slots[$pos]) > 0)
                            @php
                                if ($usedCols === 1) $w = '100%';
                                elseif ($usedCols === 2) $w = '50%';
                                else $w = '33%';
                            @endphp
                            <td style="width:{{ $w }}; vertical-align:top; text-align:{{ $pos }}; padding:0;">
                                @foreach($slots[$pos] as $block)
                                    @if($block === 'logo')
                                        <img src="{{ Storage::url($company->logo_path) }}" alt="Logo" class="logo-img">
                                    @elseif($block === 'info')
                                        <div class="company-info">
                                            <strong>{{ $company->name }}</strong><br>
                                            {{ $company->address ?? '123 Rue Exemple' }}, {{ $company->city ?? 'Libreville' }}<br>
                                            @if($company->phone)Tel: {{ $company->phone }}@endif
                                        </div>
                                        @if($style->header_content)
                                            <div style="margin-top:4px; font-size:8pt; color:#666;">{{ $style->header_content }}</div>
                                        @endif
                                    @endif
                                @endforeach
                            </td>
                        @endif
                    @endforeach
                </tr>
            </table>
        </div>

        {{-- TITRE DU DOCUMENT --}}
        @php $titlePos = $style->title_position ?? 'right'; @endphp
        <div style="text-align: {{ $titlePos }}; margin-bottom: 20px;">
            <div class="doc-title">{{ strtoupper($typeName) }}</div>
            <div class="doc-number">N 000001</div>
        </div>

        {{-- INFO CLIENT + DATES --}}
        <div class="info-section">
            <div class="info-box client">
                <div class="info-label">Client</div>
                <div class="info-value highlight">Societe Exemple SARL</div>
                <div class="info-value">123 Boulevard de l'Independance</div>
                <div class="info-value">Libreville, Gabon</div>
            </div>
            <div class="info-box" style="text-align: right;">
                <div class="info-label">Date</div>
                <div class="info-value highlight">{{ now()->format('d/m/Y') }}</div>
                <div class="info-label" style="margin-top: 10px;">Echeance</div>
                <div class="info-value highlight">{{ now()->addDays(30)->format('d/m/Y') }}</div>
            </div>
        </div>

        {{-- TABLEAU D'ARTICLES --}}
        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Quantite</th>
                    <th style="text-align:right;">Prix unitaire</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1</td><td>Service de consultation</td><td>5</td><td style="text-align:right;">50 000</td><td style="text-align:right;">250 000 FCFA</td></tr>
                <tr><td>2</td><td>Developpement web</td><td>10</td><td style="text-align:right;">75 000</td><td style="text-align:right;">750 000 FCFA</td></tr>
                <tr><td>3</td><td>Formation equipe</td><td>2</td><td style="text-align:right;">100 000</td><td style="text-align:right;">200 000 FCFA</td></tr>
                <tr><td>4</td><td>Maintenance annuelle</td><td>1</td><td style="text-align:right;">300 000</td><td style="text-align:right;">300 000 FCFA</td></tr>
            </tbody>
        </table>

        <div style="text-align:right; margin-top:15px; font-size:14pt; font-weight:bold; color:{{ $style->primary_color }};">
            Total TTC : 1 500 000 FCFA
        </div>

        {{-- PIED DE PAGE --}}
        <div class="page-footer">
            <div>{{ $style->footer_content ?? ($company->name . ' - Document genere le ' . now()->format('d/m/Y')) }}</div>
            <div>{{ strtoupper($typeName) }} N 000001 - Page 1/1</div>
        </div>
    </div>
</body>
</html>
