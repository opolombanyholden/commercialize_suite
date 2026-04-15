@extends('layouts.admin')

@section('title', 'Personnalisation des documents')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Personnalisation des documents</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">
                <i class="fas fa-palette me-2"></i>Personnalisation des documents
            </h1>
            <p class="text-muted mb-0">Personnalisez l'apparence de vos devis, factures et bons de livraison PDF.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" role="tablist">
    @foreach($documentTypes as $type => $label)
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $activeTab === $type ? 'active' : '' }}"
                id="tab-{{ $type }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $type }}"
                type="button" role="tab">
            @if($type === 'quote')
                <i class="fas fa-file-alt me-1"></i>
            @elseif($type === 'invoice')
                <i class="fas fa-file-invoice me-1"></i>
            @elseif($type === 'delivery_note')
                <i class="fas fa-truck me-1"></i>
            @else
                <i class="fas fa-receipt me-1"></i>
            @endif
            {{ $label }}
        </button>
    </li>
    @endforeach
</ul>

{{-- Tab Content --}}
<div class="tab-content">
    @foreach($documentTypes as $type => $label)
    <div class="tab-pane fade {{ $activeTab === $type ? 'show active' : '' }}"
         id="pane-{{ $type }}" role="tabpanel">
        @include('admin.document-styles._style-form', [
            'style' => $styles[$type],
            'type' => $type,
            'fonts' => $fonts,
        ])
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach(array_keys($documentTypes) as $type)
    (function(type) {
        const headColorInput = document.getElementById('table_header_color_' + type);
        const oddColorInput = document.getElementById('table_odd_row_color_' + type);
        const evenColorInput = document.getElementById('table_even_row_color_' + type);
        const textColorInput = document.getElementById('table_text_color_' + type);
        const fontSelect = document.getElementById('table_font_family_' + type);
        const fontStyleSelect = document.getElementById('table_font_style_' + type);

        function updatePreview() {
            const head = document.getElementById('previewHead_' + type);
            const body = document.getElementById('previewBody_' + type);
            if (!head || !body) return;

            head.querySelector('tr').style.backgroundColor = headColorInput.value;

            const rows = body.querySelectorAll('tr');
            rows.forEach(function(row, i) {
                row.style.backgroundColor = (i % 2 === 0) ? oddColorInput.value : evenColorInput.value;
                row.style.color = textColorInput.value;
                row.style.fontFamily = fontSelect.value;
                const fw = (fontStyleSelect.value === 'bold' || fontStyleSelect.value === 'bold_italic') ? 'bold' : 'normal';
                const fs = (fontStyleSelect.value === 'italic' || fontStyleSelect.value === 'bold_italic') ? 'italic' : 'normal';
                row.style.fontWeight = fw;
                row.style.fontStyle = fs;
            });
        }

        [headColorInput, oddColorInput, evenColorInput, textColorInput].forEach(function(el) {
            if (el) el.addEventListener('input', updatePreview);
        });
        [fontSelect, fontStyleSelect].forEach(function(el) {
            if (el) el.addEventListener('change', updatePreview);
        });
    })('{{ $type }}');
    @endforeach

    // ── Block content type toggle (show/hide custom HTML) ──
    document.querySelectorAll('.block-content-type').forEach(function(select) {
        select.addEventListener('change', function() {
            var target = this.getAttribute('data-target');
            var wrapper = document.getElementById(target + '_wrapper');
            if (wrapper) {
                var showHtml = (this.value === 'custom_html' || this.value === 'service_info');
                wrapper.style.display = showHtml ? '' : 'none';
            }
        });
    });

    // ── Block width sum validation ──
    document.querySelectorAll('.block-width').forEach(function(input) {
        input.addEventListener('input', function() {
            var section = this.getAttribute('data-section');
            var inputs = document.querySelectorAll('.block-width[data-section="' + section + '"]');
            var sum = 0;
            inputs.forEach(function(el) { sum += parseInt(el.value) || 0; });
            var indicator = document.getElementById(section + '_sum');
            if (indicator) {
                var span = indicator.querySelector('span');
                if (span) span.textContent = sum;
                indicator.style.color = (sum === 100) ? '#198754' : '#dc3545';
            }
        });
    });
});
</script>
@endpush
