@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $coverImage = $document['model_cover_image_path'] ?? $document['approved_front_image_path'] ?? $document['design_image_path'] ?? null;
@endphp

<!-- Modellino Abito -->
<div class="document-page" style="padding: 0; overflow: hidden;">
    <div style="height: 270mm; display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: middle; text-align: center;">
            @if($coverImage)
                <img src="{{ $coverImage }}" alt="Abito definitivo modellino" style="display: block; width: 100%; height: auto; max-height: 270mm; margin: 0 auto;">
            @else
                <div class="image-placeholder">Abito definitivo non disponibile</div>
            @endif
        </div>
    </div>
</div>
