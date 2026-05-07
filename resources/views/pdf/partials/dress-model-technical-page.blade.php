@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $bodyFontSize = '10px';
    $detailSections = $document['detail_sections'] ?? [];
    $frontImage = $document['front_view_image_path']
        ?? $document['technical_drawing_image_path']
        ?? $document['design_image_path']
        ?? null;
    $backImage = $document['back_view_image_path']
        ?? $document['technical_drawing_image_path']
        ?? $frontImage;
    $backDetailText = array_values(array_unique(array_merge(
        $detailSections['dietro']['text'] ?? [],
        $detailSections['chiusura']['text'] ?? [],
    )));
    $backDetailImage = $detailSections['dietro']['image_path']
        ?? ($detailSections['chiusura']['image_path'] ?? null);
    $signaturePath = file_exists(public_path('firma.png'))
        ? public_path('firma.png')
        : null;
    $technicalFrontHeight = '76mm';
    $technicalBackHeight = '76mm';
    $detailImageHeight = '28mm';
    $detailBlockMinHeight = '55mm';
    $detailBlocks = [
        ['title' => 'Dettaglio scollo',   'image' => $detailSections['scollo']['image_path']   ?? null, 'text' => $detailSections['scollo']['text']   ?? [], 'height' => $detailImageHeight],
        ['title' => 'Dettaglio maniche',  'image' => $detailSections['maniche']['image_path']  ?? null, 'text' => $detailSections['maniche']['text']  ?? [], 'height' => $detailImageHeight],
        ['title' => 'Dettaglio corpino',  'image' => $detailSections['corpino']['image_path']  ?? null, 'text' => $detailSections['corpino']['text']  ?? [], 'height' => $detailImageHeight],
        ['title' => 'Dettaglio dietro',   'image' => $backDetailImage,                                  'text' => $backDetailText,                             'height' => $detailImageHeight],
    ];
    $allMeasurements = array_merge(
        $document['measurements'] ?? [],
        collect($document['custom_measurements'] ?? [])
            ->map(fn (array $measurement): array => [
                'label' => $measurement['label'],
                'value' => $measurement['value'],
                'unit' => '',
            ])
            ->all(),
    );
@endphp

<!-- Scheda Tecnica -->
<div class="document-page" style="padding-top: 3mm; page-break-before: avoid;">
    <table style="width: 100%; height: 234mm; border-collapse: collapse; table-layout: fixed;">
        <tr>

            {{-- COLONNA 1: front + retro (43%) --}}
            <td style="width: 43%; height: 234mm; vertical-align: top; padding-right: 3mm;">
                <div>
                    <div class="section-title" style="margin-bottom: 1.5mm;">Front</div>
                    <div style="height: {{ $technicalFrontHeight }}; margin-bottom: 6mm; text-align: center; overflow: hidden;">
                        @if($frontImage)
                            <img src="{{ $frontImage }}" alt="Front" style="display: block; width: auto; max-width: 100%; height: auto; max-height: {{ $technicalFrontHeight }}; margin: 0 auto;">

                        @else
                            <div class="image-placeholder" style="padding: 14mm 4mm;">Front non disponibile</div>
                        @endif
                    </div>

                    <div class="section-title" style="margin-bottom: 1.5mm;">Retro</div>
                    <div style="height: {{ $technicalBackHeight }}; text-align: center; overflow: hidden; position: relative;">
                        @if($backImage)
                            <img src="{{ $backImage }}" alt="Retro" style="display: block; width: auto; max-width: 100%; height: auto; max-height: {{ $technicalBackHeight }}; margin: 0 auto;">
                            @if($signaturePath)
                                <img src="{{ $signaturePath }}" alt="Firma Dora Maione" style="position: absolute; right: 2mm; bottom: 1mm; width: 26mm; height: auto;">
                            @endif
                        @else
                            <div class="image-placeholder" style="padding: 14mm 4mm;">Retro non disponibile</div>
                        @endif
                    </div>
                </div>
            </td>

            {{-- COLONNA 2: dettagli immagine sopra + testo sotto --}}
            <td style="width: 32%; height: 234mm; vertical-align: top; padding: 0 3mm;">
                @foreach($detailBlocks as $index => $block)
                    <div class="section-title">{{ $block['title'] }}</div>
                    <div style="min-height: {{ $detailBlockMinHeight }}; margin-bottom: {{ $index < 3 ? '4mm' : '0' }};">
                        <div style="height: {{ $block['height'] }}; overflow: hidden; text-align: center; margin-bottom: 2mm;">
                            @if($block['image'])
                                <img src="{{ $block['image'] }}" alt="{{ $block['title'] }}" style="display: block; width: auto; max-width: 100%; height: auto; max-height: {{ $block['height'] }}; margin: 0 auto;">
                            @else
                                <div class="image-placeholder" style="padding: 5mm 2mm;">N/D</div>
                            @endif
                        </div>
                        <div style="font-size: {{ $bodyFontSize }}; line-height: 1.35; text-align: center;" class="small-text">
                            @if(! empty($block['text']))
                                @foreach($block['text'] as $line)
                                    <div style="margin-bottom: 1.2mm; text-align: center;">{{ $line }}</div>
                                @endforeach
                            @else
                                <div class="writing-lines">
                                    @for($i = 0; $i < 2; $i++)<div></div>@endfor
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </td>

            {{-- COLONNA 3: misure (24%) --}}
            <td style="width: 24%; height: 228mm; vertical-align: top; padding-left: 3mm;">
                @include('pdf.partials.dress-measure-table', [
                    'title'              => 'Misure',
                    'measurements'       => $allMeasurements,
                    'customMeasurements' => [],
                ])
                <div class="small-text" style="margin-top: 4mm; font-size: {{ $bodyFontSize }}; line-height: 1.35;">
                    <div style="margin-bottom: 2mm;"><strong>Resp.</strong> {{ $document['measurements_responsible'] ?: '' }}</div>
                    @if(! empty($document['nb_notes']))
                        <div style="font-style: italic;">{{ $document['nb_notes'] }}</div>
                    @endif
                </div>
            </td>

        </tr>
    </table>
</div>
