{{--
    Block-based header (position: fixed → repeats on every page)
    Uses document_style_blocks for content placement
    Falls back to legacy layout if uses_block_system is false
--}}
@if(isset($style) && $style->uses_block_system)
    @php
        $widths = $style->getHeaderWidths();
    @endphp
    <div class="page-header">
        <div style="display: table; width: 100%; table-layout: fixed;">
            @foreach(['left', 'center', 'right'] as $pos)
                @php $block = $style->getBlockFor('header', $pos); @endphp
                <div style="display: table-cell; width: {{ $widths[$pos] }}%; vertical-align: top; text-align: {{ $pos }};">
                    @if($block && $block->content_type !== 'empty')
                        {!! $block->render($company) !!}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@else
    {{-- Legacy fallback --}}
    @php
        $logoPos = isset($style) ? ($style->logo_position ?? 'left') : 'left';
        $infoPos = isset($style) ? ($style->company_info_position ?? 'left') : 'left';
        $slots = ['left' => [], 'center' => [], 'right' => []];
        if ($company->logo_path) $slots[$logoPos][] = 'logo';
        $slots[$infoPos][] = 'info';
        $usedCols = collect($slots)->filter(fn($s) => count($s) > 0)->count();
    @endphp
    <div class="page-header">
        <div style="display: table; width: 100%; table-layout: fixed;">
            @foreach(['left', 'center', 'right'] as $pos)
                @if(count($slots[$pos]) > 0)
                    @php
                        $w = ($usedCols === 1) ? '100%' : (($usedCols === 2) ? '50%' : '33%');
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
                                    @if($company->email){{ $company->email }}@endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
