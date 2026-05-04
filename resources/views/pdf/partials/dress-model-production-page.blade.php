@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
@endphp

<div class="document-page">
    <div class="page-title">Scheda Produzione</div>
    <div class="page-subtitle">Materiali, consumi e campioni per la lavorazione interna</div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 33.33%; vertical-align: top; padding-right: 3mm;">
                <div class="section-title">Scheda produzione</div>
                <table class="meta-table small-text">
                    <tr>
                        <td class="label">Modello</td>
                        <td>{{ $document['model_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Linea</td>
                        <td>{{ $document['line_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tipologia capo</td>
                        <td>{{ $document['garment_type'] }}</td>
                    </tr>
                </table>

                <div class="spacer-sm"></div>
                <div class="section-title">Descrizione</div>
                <div class="box small-text">
                    @forelse($document['technical_description_paragraphs'] as $paragraph)
                        <p style="margin: 0 0 2mm 0;">{{ $paragraph }}</p>
                    @empty
                        <div class="writing-lines">
                            @for($i = 0; $i < 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endforelse
                </div>

                <div class="spacer-sm"></div>
                <div class="section-title">Note produzione</div>
                <div class="box small-text">
                    @if(! empty($document['production_notes']))
                        <ul class="bullet-list">
                            @foreach($document['production_notes'] as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 4; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endif
                </div>
            </td>

            <td style="width: 33.33%; vertical-align: top; padding-right: 3mm; padding-left: 3mm;">
                <div class="section-title">Materiali</div>
                <div class="box small-text">
                    @if(! empty($document['materials']))
                        <ul class="bullet-list">
                            @foreach($document['materials'] as $material)
                                <li>{{ $material }}</li>
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

                <div class="spacer-sm"></div>
                <div class="section-title">Accessori</div>
                <div class="box small-text">
                    @if(! empty($document['accessories']))
                        <ul class="bullet-list">
                            @foreach($document['accessories'] as $accessory)
                                <li>{{ $accessory }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="writing-lines">
                            @for($i = 0; $i < 4; $i++)
                                <div></div>
                            @endfor
                        </div>
                    @endif
                </div>
            </td>

            <td style="width: 33.33%; vertical-align: top; padding-left: 3mm;">
                <div class="section-title">Consumo tessuti</div>
                <table class="grid-table small-text">
                    <tr>
                        <th>Tessuto</th>
                        <th style="width: 17%;">Altezza</th>
                        <th style="width: 20%;">Consumo</th>
                    </tr>
                    @forelse($document['consumption_rows'] as $row)
                        <tr>
                            <td>{{ $row['fabric'] }}</td>
                            <td style="text-align: center;">{{ $row['height'] }}</td>
                            <td style="text-align: center;">{{ $row['consumption'] }}</td>
                        </tr>
                    @empty
                        @for($i = 0; $i < 4; $i++)
                            <tr>
                                <td style="height: 11mm;"></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                    @endforelse
                </table>

                <div class="spacer-sm"></div>
                <div class="section-title">Campioni tessuto</div>
                @for($i = 0; $i < 3; $i++)
                    @php($sample = $document['fabric_samples'][$i] ?? null)
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3mm;">
                        <tr>
                            <td style="width: 40%; vertical-align: top; padding-right: 2mm;">
                                <div class="sample-image">
                                    @if($sample && $sample['photo_absolute_path'])
                                        <img src="{{ $sample['photo_absolute_path'] }}" alt="Campione {{ $sample['name'] }}">
                                    @endif
                                </div>
                            </td>
                            <td style="width: 60%; vertical-align: top;" class="small-text">
                                <strong>Tessuto {{ $i + 1 }}:</strong><br>
                                {{ $sample['summary'] ?? '' }}
                            </td>
                        </tr>
                    </table>
                @endfor
            </td>
        </tr>
    </table>
</div>
