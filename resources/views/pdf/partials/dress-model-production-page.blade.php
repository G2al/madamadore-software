@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $sleeveFabric = $document['sleeve_fabric'] ?? null;
    $constructionNotes = $document['construction_notes'] ?? [];
    $customMeasurements = $document['custom_measurements'] ?? [];
    $corsetSummary = $document['corset_summary'] ?? [
        'linea_sotto_seno' => '',
        'riprese_vita' => ['davanti' => '', 'lato' => '', 'dietro' => ''],
        'riprese_fianchi' => ['davanti' => '', 'lato' => '', 'dietro' => ''],
    ];
@endphp

<!-- Scheda Produzione -->
<div class="document-page" style="padding-top: 5mm;">
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; height: 236mm;">
        <tr>
            <td style="width: 33.33%; vertical-align: top; padding-right: 4mm; border-right: 1px solid #e0d7d1;">
                <div class="section-title">Scheda produzione</div>

                <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 6mm;">
                    <tr>
                        <td style="width: 28%; padding: 0 0 4mm 0;">Modello:</td>
                        <td style="padding: 0 0 4mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $document['model_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4mm 0;">Linea:</td>
                        <td style="padding: 4mm 0 4mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $document['line_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4mm 0;">Tipologia capo:</td>
                        <td style="padding: 4mm 0 4mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $document['garment_type'] }}</td>
                    </tr>
                </table>

                <div class="section-title">Descrizione</div>
                <div class="small-text" style="min-height: 80mm; line-height: 1.42;">
                    @forelse($document['technical_description_paragraphs'] as $paragraph)
                        <p style="margin: 0 0 4mm 0;">{{ $paragraph }}</p>
                    @empty
                        <div class="writing-lines">
                            @for($i = 0; $i < 6; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endforelse
                </div>

                <div class="section-title" style="margin-top: 6mm;">Note produzione</div>
                <div class="small-text" style="line-height: 1.4; min-height: 66mm;">
                    @if(! empty($document['production_notes']))
                        <ul class="bullet-list" style="padding-left: 5mm;">
                            @foreach($document['production_notes'] as $note)
                                <li style="margin-bottom: 1.3mm;">{{ $note }}</li>
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

            <td style="width: 33.33%; vertical-align: top; padding: 0 4mm; border-right: 1px solid #e0d7d1;">
                <div class="section-title">Misure personalizzate</div>
                <div class="small-text" style="line-height: 1.36; min-height: 66mm; margin-bottom: 6mm;">
                    @if(! empty($customMeasurements))
                        <table style="width: 100%; border-collapse: collapse; font-size: 8.6px;">
                            @foreach($customMeasurements as $measurement)
                                <tr>
                                    <td style="width: 58%; padding: 1.4mm 0; border-bottom: 1px solid #ece4df; vertical-align: top;">
                                        {{ $measurement['label'] }}
                                    </td>
                                    <td style="width: 42%; padding: 1.4mm 0; border-bottom: 1px solid #ece4df; text-align: right; vertical-align: top;">
                                        {{ $measurement['value'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endif
                </div>

                <div class="section-title">Misure corsetto</div>
                <div class="small-text" style="line-height: 1.3; min-height: 62mm; margin-bottom: 6mm;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8.4px; margin-bottom: 3mm;">
                        <tr>
                            <td style="width: 58%; padding: 1.5mm 0; border-bottom: 1px solid #ece4df;">Linea sotto il seno</td>
                            <td style="width: 42%; padding: 1.5mm 0; border-bottom: 1px solid #ece4df; text-align: right;">
                                {{ $corsetSummary['linea_sotto_seno'] }}
                            </td>
                        </tr>
                    </table>

                    <div style="font-weight: bold; margin-bottom: 1.2mm;">Riprese vita</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8.2px; margin-bottom: 3mm;">
                        <tr>
                            <td style="width: 33.33%; padding: 1.2mm 0; border-bottom: 1px solid #ece4df;">Davanti</td>
                            <td style="width: 33.33%; padding: 1.2mm 0; border-bottom: 1px solid #ece4df; text-align: center;">Lato</td>
                            <td style="width: 33.34%; padding: 1.2mm 0; border-bottom: 1px solid #ece4df; text-align: right;">Dietro</td>
                        </tr>
                        <tr>
                            <td style="padding: 1.4mm 0 2mm 0; border-bottom: 1px solid #ece4df;">{{ $corsetSummary['riprese_vita']['davanti'] }}</td>
                            <td style="padding: 1.4mm 0 2mm 0; border-bottom: 1px solid #ece4df; text-align: center;">{{ $corsetSummary['riprese_vita']['lato'] }}</td>
                            <td style="padding: 1.4mm 0 2mm 0; border-bottom: 1px solid #ece4df; text-align: right;">{{ $corsetSummary['riprese_vita']['dietro'] }}</td>
                        </tr>
                    </table>

                    <div style="font-weight: bold; margin-bottom: 1.2mm;">Riprese fianchi</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8.2px;">
                        <tr>
                            <td style="width: 33.33%; padding: 1.2mm 0; border-bottom: 1px solid #ece4df;">Davanti</td>
                            <td style="width: 33.33%; padding: 1.2mm 0; border-bottom: 1px solid #ece4df; text-align: center;">Lato</td>
                            <td style="width: 33.34%; padding: 1.2mm 0; border-bottom: 1px solid #ece4df; text-align: right;">Dietro</td>
                        </tr>
                        <tr>
                            <td style="padding: 1.4mm 0 2mm 0; border-bottom: 1px solid #ece4df;">{{ $corsetSummary['riprese_fianchi']['davanti'] }}</td>
                            <td style="padding: 1.4mm 0 2mm 0; border-bottom: 1px solid #ece4df; text-align: center;">{{ $corsetSummary['riprese_fianchi']['lato'] }}</td>
                            <td style="padding: 1.4mm 0 2mm 0; border-bottom: 1px solid #ece4df; text-align: right;">{{ $corsetSummary['riprese_fianchi']['dietro'] }}</td>
                        </tr>
                    </table>
                </div>

                <div class="section-title">Accessori</div>
                <div class="small-text" style="line-height: 1.42; min-height: 76mm;">
                    @if(! empty($document['accessories']))
                        <ul class="bullet-list" style="padding-left: 5mm;">
                            @foreach($document['accessories'] as $accessory)
                                <li style="margin-bottom: 4mm;">{{ $accessory }}</li>
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

            <td style="width: 33.34%; vertical-align: top; padding-left: 4mm;">
                <div class="section-title">Consumo tessuti</div>
                <table class="grid-table small-text" style="font-size: 7.6px; margin-bottom: 7mm;">
                    <tr>
                        <th>Tessuto</th>
                        <th style="width: 20%;">Altezza</th>
                        <th style="width: 24%;">Consumo</th>
                    </tr>
                    @forelse($document['consumption_rows'] as $row)
                        <tr>
                            <td>{{ $row['fabric'] }}</td>
                            <td style="text-align: center;">{{ $row['height'] }}</td>
                            <td style="text-align: center;">{{ $row['consumption'] }}</td>
                        </tr>
                    @empty
                        @for($i = 0; $i < 6; $i++)
                            <tr>
                                <td style="height: 11mm;"></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                    @endforelse
                </table>

                <div class="section-title" style="margin-top: 0;">Campioni tessuto</div>
                @for($i = 0; $i < 3; $i++)
                    @php($sample = $document['fabric_samples'][$i] ?? null)
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: {{ $i < 2 ? '7mm' : '0' }};">
                        <tr>
                            <td style="width: 34%; vertical-align: top; padding-right: 3mm;">
                                <div class="sample-image" style="height: 20mm;">
                                    @if($sample && $sample['photo_absolute_path'])
                                        <img src="{{ $sample['photo_absolute_path'] }}" alt="Campione {{ $sample['name'] }}">
                                    @endif
                                </div>
                            </td>
                            <td style="width: 66%; vertical-align: top;" class="small-text">
                                <strong>Tessuto {{ $i + 1 }}:</strong><br>
                                <div style="margin-top: 1.2mm; line-height: 1.38;">
                                    {{ $sample['summary'] ?? '' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                @endfor

                <div class="section-title" style="margin-top: 5mm;">Manica</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 4mm;">
                    <tr>
                        <td style="width: 34%; padding: 0 0 2.2mm 0;">Tessuto:</td>
                        <td style="padding: 0 0 2.2mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['name'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2.2mm 0;">Composizione:</td>
                        <td style="padding: 2.2mm 0 2.2mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['composition'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2.2mm 0;">Colore:</td>
                        <td style="padding: 2.2mm 0 2.2mm 2mm; border-bottom: 1px solid #d8ccc5;">{{ $sleeveFabric['color'] ?? '' }}</td>
                    </tr>
                </table>

                <div class="section-title">Note costruttive</div>
                <div class="small-text" style="line-height: 1.25; font-size: 9px; min-height: 30mm;">
                    @if(! empty($constructionNotes))
                        <ul class="bullet-list" style="padding-left: 5mm; margin: 0;">
                            @foreach($constructionNotes as $note)
                                <li style="margin-bottom: 1mm;">{{ $note }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 3; $i++)<div></div>@endfor
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>
