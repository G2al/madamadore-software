@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $mainFabric = $document['main_fabric'];
    $sleeveFabric = $document['sleeve_fabric'];
    $detailSections = $document['detail_sections'] ?? [];
    $frontImage = $document['front_view_image_path'] ?? $document['design_image_path'] ?? null;
    $backImage = $document['back_view_image_path'] ?? null;
@endphp

<div class="document-page">
    <div class="page-title">Scheda Tecnica</div>
    <div class="page-subtitle">Dettagli costruttivi, immagini dedicate e misure di riferimento</div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 68%; vertical-align: top; padding-right: 3mm;">
                <table class="image-grid">
                    <tr>
                        <td style="width: {{ $backImage ? '50%' : '100%' }}; padding-right: {{ $backImage ? '2mm' : '0' }};">
                            <div class="section-title">Davanti</div>
                            <div class="image-frame image-frame--portrait" style="height: 60mm;">
                                @if($frontImage)
                                    <img src="{{ $frontImage }}" alt="Vista davanti">
                                @else
                                    <div class="image-placeholder">Vista davanti non disponibile</div>
                                @endif
                            </div>
                        </td>

                        @if($backImage)
                            <td style="width: 50%; padding-left: 2mm;">
                                <div class="section-title">Dietro</div>
                                <div class="image-frame image-frame--portrait" style="height: 60mm;">
                                    <img src="{{ $backImage }}" alt="Vista dietro">
                                </div>
                            </td>
                        @endif
                    </tr>
                </table>

                <div class="spacer-sm"></div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 2mm;">
                            <div class="section-title">Dettaglio scollo</div>
                            <div class="detail-card small-text">
                                <div class="sample-image">
                                    @if(! empty($detailSections['scollo']['image_path']))
                                        <img src="{{ $detailSections['scollo']['image_path'] }}" alt="Dettaglio scollo">
                                    @endif
                                </div>

                                @if(! empty($detailSections['scollo']['text']))
                                    <ul class="bullet-list">
                                        @foreach($detailSections['scollo']['text'] as $detailLine)
                                            <li>{{ $detailLine }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 2mm;">
                            <div class="section-title">Dettaglio maniche</div>
                            <div class="detail-card small-text">
                                <div class="sample-image">
                                    @if(! empty($detailSections['maniche']['image_path']))
                                        <img src="{{ $detailSections['maniche']['image_path'] }}" alt="Dettaglio maniche">
                                    @endif
                                </div>

                                @if(! empty($detailSections['maniche']['text']))
                                    <ul class="bullet-list">
                                        @foreach($detailSections['maniche']['text'] as $detailLine)
                                            <li>{{ $detailLine }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 2mm; padding-top: 3mm;">
                            <div class="section-title">Dettaglio corpino</div>
                            <div class="detail-card small-text">
                                <div class="sample-image">
                                    @if(! empty($detailSections['corpino']['image_path']))
                                        <img src="{{ $detailSections['corpino']['image_path'] }}" alt="Dettaglio corpino">
                                    @endif
                                </div>

                                @if(! empty($detailSections['corpino']['text']))
                                    <ul class="bullet-list">
                                        @foreach($detailSections['corpino']['text'] as $detailLine)
                                            <li>{{ $detailLine }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 2mm; padding-top: 3mm;">
                            <div class="section-title">Dettaglio dietro</div>
                            <div class="detail-card small-text">
                                <div class="sample-image">
                                    @if(! empty($detailSections['dietro']['image_path']))
                                        <img src="{{ $detailSections['dietro']['image_path'] }}" alt="Dettaglio dietro">
                                    @endif
                                </div>

                                @if(! empty($detailSections['dietro']['text']))
                                    <ul class="bullet-list">
                                        @foreach($detailSections['dietro']['text'] as $detailLine)
                                            <li>{{ $detailLine }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="spacer-sm"></div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 56%; vertical-align: top; padding-right: 2mm;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 50%; vertical-align: top; padding-right: 2mm;">
                                        <div class="section-title">Tessuto principale</div>
                                        <div class="box small-text">
                                            <strong>Tessuto:</strong> {{ $mainFabric['name'] ?? '' }}<br>
                                            <strong>Composizione:</strong> {{ $mainFabric['composition'] ?? '' }}<br>
                                            <strong>Colore:</strong> {{ $mainFabric['color'] ?? '' }}
                                        </div>
                                    </td>
                                    <td style="width: 50%; vertical-align: top; padding-left: 2mm;">
                                        <div class="section-title">Manica</div>
                                        <div class="box small-text">
                                            <strong>Tessuto:</strong> {{ $sleeveFabric['name'] ?? '' }}<br>
                                            <strong>Composizione:</strong> {{ $sleeveFabric['composition'] ?? '' }}<br>
                                            <strong>Colore:</strong> {{ $sleeveFabric['color'] ?? '' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div class="spacer-sm"></div>
                            <div class="section-title">Note costruttive</div>
                            <div class="box small-text" style="min-height: 26mm;">
                                @if(! empty($document['construction_notes']))
                                    <ul class="bullet-list">
                                        @foreach($document['construction_notes'] as $note)
                                            <li>{{ $note }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 3; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td style="width: 44%; vertical-align: top; padding-left: 2mm;">
                            <div class="section-title">Chiusura</div>
                            <div class="detail-card small-text">
                                <div class="sample-image" style="height: 24mm;">
                                    @if(! empty($detailSections['chiusura']['image_path']))
                                        <img src="{{ $detailSections['chiusura']['image_path'] }}" alt="Dettaglio chiusura">
                                    @endif
                                </div>

                                @if(! empty($detailSections['chiusura']['text']))
                                    <ul class="bullet-list">
                                        @foreach($detailSections['chiusura']['text'] as $detailLine)
                                            <li>{{ $detailLine }}</li>
                                        @endforeach
                                    </ul>
                                @elseif(! empty($document['closure_details']))
                                    <div class="paragraph-list">
                                        <p>{{ $document['closure_details'] }}</p>
                                    </div>
                                @else
                                    <div class="writing-lines">
                                        @for($i = 0; $i < 2; $i++)
                                            <div></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 32%; vertical-align: top; padding-left: 3mm;">
                @include('pdf.partials.dress-measure-table', [
                    'title' => 'Misure',
                    'measurements' => $document['measurements'],
                    'customMeasurements' => $document['custom_measurements'],
                ])

                <div class="spacer-sm"></div>
                <table class="meta-table small-text">
                    <tr>
                        <td class="label">Responsabile misure</td>
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
