@php
    $showSignature = $withSignature ?? false;
    $signaturePath = ($showSignature && $company->signature_image)
        ? public_path('storage/' . $company->signature_image)
        : null;
    $clientName = $clientLabel ?? '........................';
@endphp
<div style="margin-top: 25px; display: table; width: 100%;">
    <div style="display: table-cell; width: 45%; padding: 15px; border: 1px solid #ddd; text-align: center;">
        <div style="font-size: 9pt; color: #666; margin-bottom: {{ $signaturePath ? '10px' : '60px' }};">Pour {{ $company->name }}</div>
        @if($signaturePath && file_exists($signaturePath))
            <div style="margin-bottom: 5px;">
                <img src="{{ $signaturePath }}" style="max-height: 50px; max-width: 150px;">
            </div>
        @endif
    </div>
    <div style="display: table-cell; width: 45%; padding: 15px; border: 1px solid #ddd; text-align: center; margin-left: 10%;">
        <div style="font-size: 9pt; color: #666; margin-bottom: 60px;">Pour {{ $clientName }}</div>
    </div>
</div>
