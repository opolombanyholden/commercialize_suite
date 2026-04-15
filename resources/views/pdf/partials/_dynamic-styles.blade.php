{{-- Dynamic styles injected from DocumentStyle settings --}}
@if(isset($style))
    body {
        font-family: '{{ $style->table_font_family ?? 'DejaVu Sans' }}', Arial, sans-serif;
        @if($style->background_color)
        background-color: {{ $style->background_color }};
        @endif
    }
    @if($style->background_image)
    .bg-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url('{{ public_path('storage/' . $style->background_image) }}');
        background-size: cover;
        background-repeat: no-repeat;
        opacity: 0.15;
        z-index: -2;
    }
    @endif
    .page-header {
        border-bottom-color: {{ $style->primary_color }};
    }
    .doc-title {
        color: {{ $style->primary_color }};
    }
    .info-box.client {
        border-left-color: {{ $style->client_box_border_color ?? $style->primary_color }};
        @if($style->client_box_bg_color)
        background-color: {{ $style->client_box_bg_color }};
        @endif
    }
    table.items thead {
        background-color: {{ $style->table_header_color }};
    }
    table.items td {
        color: {{ $style->table_text_color ?? '#333333' }};
        font-weight: {{ $style->font_weight }};
        font-style: {{ $style->font_style_css }};
    }
    table.items tbody tr:nth-child(odd) {
        background-color: {{ $style->table_odd_row_color ?? '#FFFFFF' }};
    }
    table.items tbody tr:nth-child(even) {
        background-color: {{ $style->table_even_row_color ?? '#FAFAFA' }};
    }
    .totals-row.grand {
        border-top-color: {{ $style->table_header_color }};
        color: {{ $style->primary_color }};
    }
    .amount-words {
        border-left-color: {{ $style->primary_color }};
    }
@endif
