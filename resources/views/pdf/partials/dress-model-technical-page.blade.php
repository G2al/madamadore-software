@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);

    $bodyFontSize = '9px';
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

    /*
    |--------------------------------------------------------------------------
    | Layout fisso pagina tecnica
    |--------------------------------------------------------------------------
    | La struttura comanda. I contenuti si adattano.
    */
    $pageContentHeight = '252mm';

    $sideBlockHeight = '126mm';
    $sideImageHeight = '106mm';
    $retroTopSpacing = '22mm';
    $retroImageTopOffset = '6mm';
$retroImageMaxHeight = '100mm';

    $detailBlockHeight = '63mm';
    $detailImageHeight = '30mm';
    $detailTextHeight = '21mm';

    $measureRowsHeight = '224mm';
    $measureFooterHeight = '28mm';

    $detailBlocks = [
        [
            'title' => 'Dettaglio scollo',
            'image' => $detailSections['scollo']['image_path'] ?? null,
            'text' => $detailSections['scollo']['text'] ?? [],
        ],
        [
            'title' => 'Dettaglio maniche',
            'image' => $detailSections['maniche']['image_path'] ?? null,
            'text' => $detailSections['maniche']['text'] ?? [],
        ],
        [
            'title' => 'Dettaglio corpino',
            'image' => $detailSections['corpino']['image_path'] ?? null,
            'text' => $detailSections['corpino']['text'] ?? [],
        ],
        [
            'title' => 'Dettaglio dietro',
            'image' => $backDetailImage,
            'text' => $backDetailText,
        ],
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

    $measurementCount = max(count($allMeasurements), 1);

    /*
    |--------------------------------------------------------------------------
    | Altezza righe misure
    |--------------------------------------------------------------------------
    | Più misure ci sono, più la riga si compatta.
    | Con le misure standard resta abbastanza alta da occupare la colonna.
    */
    $measurementRowHeight = match (true) {
        $measurementCount <= 30 => '6.15mm',
        $measurementCount <= 34 => '5.75mm',
        $measurementCount <= 38 => '5.35mm',
        default => '4.85mm',
    };

    $nbNotes = trim((string) ($document['nb_notes'] ?? ''));
    $shortNbNotes = mb_strlen($nbNotes) > 145
        ? mb_substr($nbNotes, 0, 145) . '...'
        : $nbNotes;
@endphp

<!-- Scheda Tecnica -->
<div class="document-page" style="padding-top: 3mm; page-break-before: avoid;">
    <table style="width: 100%; height: {{ $pageContentHeight }}; border-collapse: collapse; table-layout: fixed;">
        <tr>
            {{-- COLONNA 1: Front / Retro --}}
            <td style="width: 43%; height: {{ $pageContentHeight }}; vertical-align: top; padding-right: 3mm;">
                <table style="width: 100%; height: {{ $pageContentHeight }}; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="height: {{ $sideBlockHeight }}; vertical-align: top;">
                            <div class="section-title" style="margin-bottom: 1.5mm;">Front</div>

                            <div style="height: {{ $sideImageHeight }}; text-align: center; overflow: visible;">
                                @if($frontImage)
                                    <img
                                        src="{{ $frontImage }}"
                                        alt="Front"
                                        style="display: block; width: auto; height: auto; max-width: 100%; max-height: {{ $sideImageHeight }}; margin: 0 auto;"
                                    >
                                @else
                                    <div class="image-placeholder" style="height: {{ $sideImageHeight }}; padding: 34mm 4mm 0 4mm;">
                                        Front non disponibile
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="height: {{ $sideBlockHeight }}; vertical-align: top; padding-top: {{ $retroTopSpacing }};">
                            <div class="section-title" style="margin-bottom: 1.5mm;">Retro</div>
                            <div style="height: {{ $sideImageHeight }}; text-align: center; overflow: visible; position: relative;">
    @if($backImage)
        <div style="height: {{ $sideImageHeight }}; padding-top: {{ $retroImageTopOffset }}; box-sizing: border-box;">
            <img
                src="{{ $backImage }}"
                alt="Retro"
                style="display: block; width: auto; height: auto; max-width: 100%; max-height: {{ $retroImageMaxHeight }}; margin: 0 auto;"
            >
        </div>

        @if($signaturePath)
            <img
                src="{{ $signaturePath }}"
                alt="Firma Dora Maione"
                style="position: absolute; right: 2mm; bottom: 1mm; width: 26mm; height: auto;"
            >
        @endif
    @else
        <div class="image-placeholder" style="height: {{ $sideImageHeight }}; padding: 34mm 4mm 0 4mm;">
            Retro non disponibile
        </div>
    @endif
</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- COLONNA 2: Dettagli tecnici --}}
            <td style="width: 32%; height: {{ $pageContentHeight }}; vertical-align: top; padding: 0 3mm;">
                <table style="width: 100%; height: {{ $pageContentHeight }}; border-collapse: collapse; table-layout: fixed;">
                    @foreach($detailBlocks as $block)
                        @php
                            $textLines = array_slice($block['text'] ?? [], 0, 3);
                            $hasMoreText = count($block['text'] ?? []) > count($textLines);
                        @endphp

                        <tr>
                            <td style="height: {{ $detailBlockHeight }}; vertical-align: top;">
                                <div class="section-title" style="margin-bottom: 1.2mm;">{{ $block['title'] }}</div>

                                <div style="height: {{ $detailImageHeight }}; overflow: hidden; text-align: center; margin-bottom: 1.6mm;">
                                    @if($block['image'])
                                        <img
                                            src="{{ $block['image'] }}"
                                            alt="{{ $block['title'] }}"
                                            style="display: block; width: auto; height: {{ $detailImageHeight }}; max-width: 100%; margin: 0 auto;"
                                        >
                                    @else
                                        <div style="height: {{ $detailImageHeight }};">
                                            <div class="writing-lines" style="padding-top: 10mm;">
                                                <div style="height: 4mm;"></div>
                                                <div style="height: 4mm;"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div
                                    class="small-text"
                                    style="height: {{ $detailTextHeight }}; font-size: {{ $bodyFontSize }}; line-height: 1.25; text-align: center; overflow: hidden;"
                                >
                                    @if(! empty($textLines))
                                        @foreach($textLines as $line)
                                            <div style="margin-bottom: 0.8mm;">{{ $line }}</div>
                                        @endforeach

                                        @if($hasMoreText)
                                            <div>...</div>
                                        @endif
                                    @else
                                        <div class="writing-lines" style="margin-top: 1mm;">
                                            <div style="height: 4.3mm;"></div>
                                            <div style="height: 4.3mm;"></div>
                                            <div style="height: 4.3mm;"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>

            {{-- COLONNA 3: Misure + Resp/Note --}}
            <td style="width: 24%; height: {{ $pageContentHeight }}; vertical-align: top; padding-left: 3mm;">
                <table style="width: 100%; height: {{ $pageContentHeight }}; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="height: {{ $measureRowsHeight }}; vertical-align: top;">
                            <table style="width: 100%; height: {{ $measureRowsHeight }}; border-collapse: collapse; table-layout: fixed;">
                                <tr>
                                    <th colspan="2" style="text-align: left; font-size: 9.2px; padding: 0 0 1.1mm 0; border-bottom: 1px solid #d8ccc5; text-transform: uppercase; letter-spacing: 0.05em;">
                                        Misure
                                    </th>
                                </tr>

                                @foreach($allMeasurements as $measurement)
                                    <tr style="height: {{ $measurementRowHeight }};">
                                        <td style="width: 57%; height: {{ $measurementRowHeight }}; padding: 0.35mm 0; border-bottom: 0.4pt solid #ebe4e0; font-size: 8px; line-height: 1.08; vertical-align: middle;">
                                            {{ $measurement['label'] }}
                                        </td>
                                        <td style="width: 43%; height: {{ $measurementRowHeight }}; text-align: right; padding: 0.35mm 0; border-bottom: 0.4pt solid #ebe4e0; font-size: 8px; line-height: 1.08; white-space: nowrap; vertical-align: middle;">
                                            {{ $measurement['value'] !== '' ? trim($measurement['value'] . ' ' . $measurement['unit']) : '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="height: {{ $measureFooterHeight }}; vertical-align: top; padding-top: 2.5mm;">
                            <div style="font-size: 7.8px; line-height: 1.22;">
                                <div style="margin-bottom: 2mm; border-bottom: 0.4pt solid #ebe4e0; padding-bottom: 1mm;">
                                    <strong>Resp.</strong>
                                    @if(! empty($document['measurements_responsible']))
                                        {{ $document['measurements_responsible'] }}
                                    @else
                                        &nbsp;
                                    @endif
                                </div>

                                <div style="font-weight: bold; margin-bottom: 1mm;">Note</div>

                                @if($shortNbNotes !== '')
                                    <div style="font-style: italic;">
                                        {{ $shortNbNotes }}
                                    </div>
                                @else
                                    <div class="writing-lines">
                                        <div style="height: 4.5mm;"></div>
                                        <div style="height: 4.5mm;"></div>
                                        <div style="height: 4.5mm;"></div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>