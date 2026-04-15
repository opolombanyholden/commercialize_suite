{{--
    Bloc titre du document + N° + dates
    Positionne selon title_position (left / center / right)
    S'affiche SOUS l'en-tete, dans le corps du document
--}}
@php
    $titlePos = isset($style) ? ($style->title_position ?? 'right') : 'right';
@endphp

<div class="doc-title-block" style="text-align: {{ $titlePos }}; margin-bottom: 20px;">
    <div class="doc-title">{{ $docTitle }}</div>
    <div class="doc-number">N {{ $docNumber }}</div>
    @if(isset($titleExtra) && $titleExtra)
        {!! $titleExtra !!}
    @endif
</div>
