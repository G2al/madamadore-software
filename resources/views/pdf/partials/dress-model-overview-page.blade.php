@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $coverImage = $document['model_cover_image_path'] ?? $document['approved_front_image_path'] ?? $document['design_image_path'] ?? null;
    $signaturePath = file_exists(public_path('firma.png'))
        ? public_path('firma.png')
        : null;
@endphp

<!-- Modellino Abito -->
<div class="document-page" style="padding: 0; overflow: hidden;">
    <div style="height: 188mm; display: table; width: 100%; position: relative;">
        <div style="display: table-cell; vertical-align: middle; text-align: center;">
            @if($coverImage)
                <img src="{{ $coverImage }}" alt="Abito definitivo modellino" style="display: block; width: 118%; height: auto; max-height: 280mm; margin: 0 0 0 35mm;">
            @else
                <div class="image-placeholder">Abito definitivo non disponibile</div>
            @endif
        </div>
        @if($coverImage && $signaturePath)
            <img src="{{ $signaturePath }}" alt="Firma Dora Maione" style="position: absolute; right: 8mm; bottom: -80mm; width: 34mm; height: auto;">
        @endif
    </div>
</div>
