@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $mainFabric = $document['main_fabric'];
    $sleeveFabric = $document['sleeve_fabric'];
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
    $technicalFrontHeight = '51mm';
    $technicalBackHeight = '51mm';
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

            {{-- COLONNA 1: front/retro + tessuti + note (43%) --}}
            <td style="width: 43%; height: 234mm; vertical-align: top; padding-right: 3mm;">
                <div style="margin-bottom: 4mm;">
                    <div class="section-title" style="margin-bottom: 1.5mm;">Front</div>
                    <div style="height: {{ $technicalFrontHeight }}; margin-bottom: 3mm; text-align: center; overflow: hidden;">
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

                <div class="section-title">Tessuto principale</div>
                <table style="width: 100%; border-collapse: collapse; font-size: {{ $bodyFontSize }}; margin-bottom: 5mm;">
                    <tr>
                        <td style="width: 32%; padding: 0 0 3mm 0;">Tessuto:</td>
                        <td style="padding: 0 0 3mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $mainFabric['name'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3mm 0;">Composizione:</td>
                        <td style="padding: 3mm 0 3mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $mainFabric['composition'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3mm 0;">Colore:</td>
                        <td style="padding: 3mm 0 3mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $mainFabric['color'] ?? '' }}</td>
                    </tr>
                </table>

                <div class="section-title">Manica</div>
                <table style="width: 100%; border-collapse: collapse; font-size: {{ $bodyFontSize }}; margin-bottom: 5mm;">
                    <tr>
                        <td style="width: 32%; padding: 0 0 3mm 0;">Tessuto:</td>
                        <td style="padding: 0 0 3mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['name'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3mm 0;">Composizione:</td>
                        <td style="padding: 3mm 0 3mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['composition'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3mm 0;">Colore:</td>
                        <td style="padding: 3mm 0 3mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['color'] ?? '' }}</td>
                    </tr>
                </table>

                <div class="section-title">Note costruttive</div>
                <div class="small-text" style="line-height: 1.3; font-size: {{ $bodyFontSize }}; min-height: 64mm;">
                    @if(! empty($document['construction_notes']))
                        <ul class="bullet-list" style="padding-left: 5mm; margin: 0;">
                            @foreach($document['construction_notes'] as $note)
                                <li style="margin-bottom: 1.4mm;">{{ $note }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 7; $i++)<div></div>@endfor
                        </div>
                    @endif
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
                <table class="meta-table small-text" style="margin-top: 4mm; font-size: {{ $bodyFontSize }};">
                    <tr>
                        <td class="label">Respons. misure</td>
                        <td>{{ $document['measurements_responsible'] ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">N.B.</td>
                        <td>{{ $document['nb_notes'] ?: '' }}</td>
                    </tr>
                </table>
            </td>

        </tr>
    </table>
</div>
