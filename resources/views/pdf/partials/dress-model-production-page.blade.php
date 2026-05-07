@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
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
                <div class="section-title">Materiali</div>
                <div class="small-text" style="min-height: 138mm; line-height: 1.42;">
                    @if(! empty($document['materials']))
                        <ul class="bullet-list" style="padding-left: 5mm;">
                            @foreach($document['materials'] as $material)
                                <li style="margin-bottom: 4mm;">{{ $material }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 8; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endif
                </div>

                <div class="section-title" style="margin-top: 6mm;">Accessori</div>
                <div class="small-text" style="line-height: 1.42; min-height: 72mm;">
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
                            <td style="width: 42%; vertical-align: top; padding-right: 3mm;">
                                <div class="sample-image" style="height: 33mm;">
                                    @if($sample && $sample['photo_absolute_path'])
                                        <img src="{{ $sample['photo_absolute_path'] }}" alt="Campione {{ $sample['name'] }}">
                                    @endif
                                </div>
                            </td>
                            <td style="width: 58%; vertical-align: top;" class="small-text">
                                <strong>Tessuto {{ $i + 1 }}:</strong><br>
                                {{ $sample['summary'] ?? '' }}
                                <div class="writing-lines" style="margin-top: 3mm;">
                                    <div style="height: 5mm;"></div>
                                    <div style="height: 5mm;"></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                @endfor
            </td>
        </tr>
    </table>
</div>
