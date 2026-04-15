@if(isset($document) && $document->subject)
<div style="margin-bottom: 15px; padding: 8px 0; font-size: 11pt;">
    <strong>Objet :</strong> {{ $document->subject }}
</div>
@endif
