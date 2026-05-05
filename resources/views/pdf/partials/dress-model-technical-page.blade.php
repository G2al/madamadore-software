@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $mainFabric = $document['main_fabric'];
    $sleeveFabric = $document['sleeve_fabric'];
    $detailSections = $document['detail_sections'] ?? [];
    $frontImage = $document['front_view_image_path'] ?? $document['design_image_path'] ?? null;
    $backImage = $document['back_view_image_path'] ?? $frontImage;
    $backDetailText = array_values(array_unique(array_merge(
        $detailSections['dietro']['text'] ?? [],
        $detailSections['chiusura']['text'] ?? [],
    )));
    $backDetailImage = $detailSections['dietro']['image_path']
        ?? ($detailSections['chiusura']['image_path'] ?? null);
    $detailBlocks = [
        [
            'title' => 'Dettaglio scollo',
            'image' => $detailSections['scollo']['image_path'] ?? null,
            'text' => $detailSections['scollo']['text'] ?? [],
            'height' => '28mm',
        ],
        [
            'title' => 'Dettaglio maniche',
            'image' => $detailSections['maniche']['image_path'] ?? null,
            'text' => $detailSections['maniche']['text'] ?? [],
            'height' => '28mm',
        ],
        [
            'title' => 'Dettaglio corpino',
            'image' => $detailSections['corpino']['image_path'] ?? null,
            'text' => $detailSections['corpino']['text'] ?? [],
            'height' => '28mm',
        ],
        [
            'title' => 'Dettaglio dietro',
            'image' => $backDetailImage,
            'text' => $backDetailText,
            'height' => '28mm',
        ],
    ];
@endphp

<!-- Scheda Tecnica -->
<div class="document-page" style="padding-top: 5mm;">
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <tr>
            <td style="width: 42%; vertical-align: top; padding-right: 4mm;">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="width: 50%; padding-right: 2mm; vertical-align: top;">
                            @if($frontImage)
                                <img src="{{ $frontImage }}" alt="Vista davanti" style="display: block; width: 100%; height: auto; max-height: 92mm; margin: 0 auto;">
                            @else
                                <div class="image-placeholder" style="padding: 34mm 4mm;">Vista davanti non disponibile</div>
                            @endif
                        </td>
                        <td style="width: 50%; padding-left: 2mm; vertical-align: top;">
                            @if($backImage)
                                <img src="{{ $backImage }}" alt="Vista dietro" style="display: block; width: 100%; height: auto; max-height: 92mm; margin: 0 auto;">
                            @else
                                <div class="image-placeholder" style="padding: 34mm 4mm;">Vista dietro non disponibile</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="spacer-sm"></div>

                <div class="section-title">Tessuto principale</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 4mm;">
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
                <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 4mm;">
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
                <div class="small-text" style="line-height: 1.38;">
                    @if(! empty($document['construction_notes']))
                        <ul class="bullet-list" style="padding-left: 5mm;">
                            @foreach($document['construction_notes'] as $note)
                                <li style="margin-bottom: 1.2mm;">{{ $note }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endif
                </div>
            </td>

            <td style="width: 31%; vertical-align: top; padding: 0 4mm;">
                @foreach($detailBlocks as $index => $block)
                    <div class="section-title">{{ $block['title'] }}</div>
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: {{ $index < 3 ? '4mm' : '0' }};">
                        <tr>
                            <td style="width: 44%; vertical-align: top; padding-right: 3mm;">
                                <div style="height: {{ $block['height'] }}; overflow: hidden; text-align: center;">
                                    @if($block['image'])
                                        <img src="{{ $block['image'] }}" alt="{{ $block['title'] }}" style="display: block; width: 100%; height: auto; max-height: {{ $block['height'] }}; margin: 0 auto;">
                                    @else
                                        <div class="image-placeholder" style="padding: 10mm 2mm;">Immagine non disponibile</div>
                                    @endif
                                </div>
                            </td>
                            <td style="width: 56%; vertical-align: top;" class="small-text">
                                @if(! empty($block['text']))
                                    <ul class="bullet-list" style="padding-left: 4mm; margin-top: 0;">
                                        @foreach($block['text'] as $detailLine)
                                            <li style="margin-bottom: 1.3mm;">{{ $detailLine }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                @endforeach
            </td>

            <td style="width: 27%; vertical-align: top; padding-left: 4mm;">
                @include('pdf.partials.dress-measure-table', [
                    'title' => 'Misure',
                    'measurements' => $document['measurements'],
                    'customMeasurements' => [],
                ])

                <div class="spacer-sm"></div>
                <table class="meta-table small-text">
                    <tr>
                        <td class="label">Respon. misure</td>
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
