{{--
    Block-based footer (position: fixed → repeats on every page)
--}}
@if(isset($style) && $style->uses_block_system)
    @php $widths = $style->getFooterWidths(); @endphp
    <div class="page-footer">
        <div style="display: table; width: 100%; table-layout: fixed;">
            @foreach(['left', 'center', 'right'] as $pos)
                @php $block = $style->getBlockFor('footer', $pos); @endphp
                <div style="display: table-cell; width: {{ $widths[$pos] }}%; vertical-align: top; text-align: {{ $pos }}; font-size: 8pt; color: #999;">
                    @if($block && $block->content_type !== 'empty')
                        {!! $block->render($company) !!}
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@else
    {{-- Legacy fallback --}}
    <div class="page-footer">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell;">
                @if(isset($style) && $style->footer_content)
                    {{ $style->footer_content }}
                @else
                    {{ $company->name }} - Document genere le {{ now()->format('d/m/Y a H:i') }}
                @endif
            </div>
            <div style="display: table-cell; text-align: right;">
                {{ $footerRight ?? '' }}
            </div>
        </div>
    </div>
@endif
