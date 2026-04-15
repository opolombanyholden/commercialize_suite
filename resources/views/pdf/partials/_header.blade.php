{{--
    En-tete de page (position: fixed → repete sur chaque page)
    2 blocs independants :
      - Bloc Logo (logo_position)
      - Bloc Denomination + Infos entreprise (company_info_position)
    Chacun aligne sur left / center / right via un systeme 3 colonnes table-cell
--}}
@php
    $logoPos = isset($style) ? ($style->logo_position ?? 'left') : 'left';
    $infoPos = isset($style) ? ($style->company_info_position ?? 'left') : 'left';

    // Construire les 3 slots
    $slots = ['left' => [], 'center' => [], 'right' => []];

    if ($company->logo_path) {
        $slots[$logoPos][] = 'logo';
    }
    $slots[$infoPos][] = 'info';

    // Compter les colonnes occupees
    $usedCols = collect($slots)->filter(fn($s) => count($s) > 0)->count();
@endphp

<div class="page-header">
    <div style="display: table; width: 100%; table-layout: fixed;">
        @foreach(['left', 'center', 'right'] as $pos)
            @if(count($slots[$pos]) > 0)
                @php
                    if ($usedCols === 1) $w = '100%';
                    elseif ($usedCols === 2) $w = '50%';
                    else $w = '33%';
                @endphp
                <div style="display: table-cell; width: {{ $w }}; vertical-align: top; text-align: {{ $pos }};">
                    @foreach($slots[$pos] as $block)
                        @if($block === 'logo')
                            <img src="{{ public_path('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="logo">
                        @elseif($block === 'info')
                            <div class="company-info">
                                <strong>{{ $company->name }}</strong><br>
                                @if($company->legal_name){{ $company->legal_name }}<br>@endif
                                @if($company->address){{ $company->address }}<br>@endif
                                @if($company->postal_code || $company->city){{ $company->postal_code }} {{ $company->city }}<br>@endif
                                @if($company->phone)Tel: {{ $company->phone }}<br>@endif
                                @if($company->email){{ $company->email }}<br>@endif
                                @if($company->tax_id)N Fiscal: {{ $company->tax_id }}@endif
                            </div>
                            @if(isset($style) && $style->header_content)
                                <div style="margin-top: 4px; font-size: 8pt; color: #666;">{{ $style->header_content }}</div>
                            @endif
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
