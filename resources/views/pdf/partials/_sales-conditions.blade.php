@php
    // Priorite : conditions du style > conditions entreprise > terms du document
    $conditions = isset($style) ? $style->getEffectiveSalesConditions() : null;
    if (!$conditions && isset($document) && $document->terms) {
        $conditions = $document->terms;
    }
    $condBg = isset($style) && $style->conditions_bg_color ? $style->conditions_bg_color : '#f5f5f5';
    $condBorder = isset($style) && $style->conditions_border_color ? $style->conditions_border_color : '#dddddd';
    $condWidth = isset($style) && $style->conditions_width ? $style->conditions_width : 100;
@endphp
@if($conditions)
<div style="margin-top: 15px; padding: 12px; background-color: {{ $condBg }}; border: 1px solid {{ $condBorder }}; font-size: 9pt; width: {{ $condWidth }}%;">
    <strong>Conditions de vente :</strong><br>
    {!! nl2br(e($conditions)) !!}
</div>
@endif
