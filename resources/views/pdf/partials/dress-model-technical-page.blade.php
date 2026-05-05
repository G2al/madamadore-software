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
    $frontBackImageHeight = '128mm';
    $detailImageHeight = '59mm';
    $detailBlockMinHeight = '53mm';
    $detailBlocks = [
        ['title' => 'Dettaglio scollo',   'image' => $detailSections['scollo']['image_path']   ?? null, 'text' => $detailSections['scollo']['text']   ?? [], 'height' => $detailImageHeight],
        ['title' => 'Dettaglio maniche',  'image' => $detailSections['maniche']['image_path']  ?? null, 'text' => $detailSections['maniche']['text']  ?? [], 'height' => $detailImageHeight],
        ['title' => 'Dettaglio corpino',  'image' => $detailSections['corpino']['image_path']  ?? null, 'text' => $detailSections['corpino']['text']  ?? [], 'height' => $detailImageHeight],
        ['title' => 'Dettaglio dietro',   'image' => $backDetailImage,                                  'text' => $backDetailText,                             'height' => $detailImageHeight],
    ];
@endphp

<!-- Scheda Tecnica -->
<div class="document-page" style="padding-top: 3mm; page-break-before: avoid;">
    <table style="width: 100%; height: 234mm; border-collapse: collapse; table-layout: fixed;">
        <tr>

            {{-- COLONNA 1: immagini + tessuti + note (43%) --}}
            <td style="width: 43%; height: 234mm; vertical-align: top; padding-right: 3mm;">

                <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 2mm;">
                    <tr>
                        <td style="width: 50%; padding-right: 2mm; vertical-align: top;">
                            <div style="height: {{ $frontBackImageHeight }}; text-align: center; overflow: hidden;">
                                @if($frontImage)
                                    <img src="{{ $frontImage }}" alt="Vista davanti" style="display: block; width: 100%; height: auto; max-height: {{ $frontBackImageHeight }}; margin: 0 auto;">
                                @else
                                    <div class="image-placeholder" style="padding: 32mm 4mm;">Vista davanti non disponibile</div>
                                @endif
                            </div>
                        </td>
                        <td style="width: 50%; padding-left: 2mm; vertical-align: top;">
                            <div style="height: {{ $frontBackImageHeight }}; text-align: center; overflow: hidden;">
                                @if($backImage)
                                    <img src="{{ $backImage }}" alt="Vista dietro" style="display: block; width: 100%; height: auto; max-height: {{ $frontBackImageHeight }}; margin: 0 auto;">
                                @else
                                    <div class="image-placeholder" style="padding: 32mm 4mm;">Vista dietro non disponibile</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="section-title">Tessuto principale</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 3mm;">
                    <tr>
                        <td style="width: 32%; padding: 0 0 2.5mm 0;">Tessuto:</td>
                        <td style="padding: 0 0 2.5mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $mainFabric['name'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2.5mm 0;">Composizione:</td>
                        <td style="padding: 2.5mm 0 2.5mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $mainFabric['composition'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2.5mm 0;">Colore:</td>
                        <td style="padding: 2.5mm 0 2.5mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $mainFabric['color'] ?? '' }}</td>
                    </tr>
                </table>

                <div class="section-title">Manica</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 3mm;">
                    <tr>
                        <td style="width: 32%; padding: 0 0 2.5mm 0;">Tessuto:</td>
                        <td style="padding: 0 0 2.5mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['name'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2.5mm 0;">Composizione:</td>
                        <td style="padding: 2.5mm 0 2.5mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['composition'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2.5mm 0;">Colore:</td>
                        <td style="padding: 2.5mm 0 2.5mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['color'] ?? '' }}</td>
                    </tr>
                </table>

                <div class="section-title">Note costruttive</div>
                <div class="small-text" style="line-height: 1.22; font-size: 10px; min-height: 56mm;">
                    @if(! empty($document['construction_notes']))
                        <ul class="bullet-list" style="padding-left: 5mm; margin: 0;">
                            @foreach($document['construction_notes'] as $note)
                                <li style="margin-bottom: 1mm;">{{ $note }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 6; $i++)<div></div>@endfor
                        </div>
                    @endif
                </div>
            </td>

            {{-- COLONNA 2: dettagli (33%) --}}
            <td style="width: 32%; height: 234mm; vertical-align: top; padding: 0 3mm;">
                @foreach($detailBlocks as $index => $block)
                    <div class="section-title">{{ $block['title'] }}</div>
                    <table style="width: 100%; min-height: {{ $detailBlockMinHeight }}; border-collapse: collapse; table-layout: fixed; margin-bottom: {{ $index < 3 ? '2.4mm' : '0' }};">
                        <tr>
                            <td style="width: 44%; vertical-align: top; padding-right: 2mm;">
                                <div style="height: {{ $block['height'] }}; overflow: hidden; text-align: center;">
                                    @if($block['image'])
                                        <img src="{{ $block['image'] }}" alt="{{ $block['title'] }}" style="display: block; width: 100%; height: auto; max-height: {{ $block['height'] }}; margin: 0 auto;">
                                    @else
                                        <div class="image-placeholder" style="padding: 9mm 2mm;">N/D</div>
                                    @endif
                                </div>
                            </td>
                            <td style="width: 56%; vertical-align: top; padding-top: 0.5mm; font-size: 6.9px;" class="small-text">
                                @if(! empty($block['text']))
                                    <ul class="bullet-list" style="padding-left: 3mm; margin-top: 0;">
                                        @foreach($block['text'] as $line)
                                            <li style="margin-bottom: 0.8mm;">{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)<div></div>@endfor
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                @endforeach
            </td>

            {{-- COLONNA 3: misure (24%) --}}
            <td style="width: 25%; height: 234mm; vertical-align: top; padding-left: 3mm;">
                @include('pdf.partials.dress-measure-table', [
                    'title'              => 'Misure',
                    'measurements'       => $document['measurements'],
                    'customMeasurements' => [],
                ])
                <table class="meta-table small-text" style="margin-top: 3mm; font-size: 7px;">
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
