@extends('pdf.layouts.dress-document', ['title' => 'Preventivo Abito #' . $dress->id])

@php
    $frontImage = $document['overview_front_image_path'] ?? $document['design_image_path'] ?? null;
    $backImage = $document['overview_back_image_path'] ?? null;
@endphp

@section('content')
    <div class="document-page">
        <div class="page-title">Scheda Cliente</div>
        <div class="page-subtitle">Preventivo cliente e bozzetto approvativo</div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 42%; vertical-align: top; padding-right: 3mm;">
                    <table class="meta-table small-text">
                        <tr>
                            <td class="label">Preventivo Nr.</td>
                            <td>{{ $dress->id }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nome e cognome</td>
                            <td>{{ $dress->customer_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Telefono</td>
                            <td>{{ $dress->phone_number }}</td>
                        </tr>
                        <tr>
                            <td class="label">Cerimonia</td>
                            <td>{{ $dress->ceremony_type ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Intestatario cerimonia</td>
                            <td>{{ $dress->ceremony_holder ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Data cerimonia</td>
                            <td>{{ $dress->ceremony_date?->format('d/m/Y') ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Data consegna</td>
                            <td>{{ $dress->delivery_date?->format('d/m/Y') ?: '-' }}</td>
                        </tr>
                    </table>

                    <div class="spacer-sm"></div>
                    <div class="section-title">Descrizione abito</div>
                    <div class="box small-text" style="min-height: 38mm;">
                        <div class="paragraph-list">
                            @forelse($document['description_paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <div class="writing-lines">
                                    @for($i = 0; $i < 4; $i++)
                                        <div></div>
                                    @endfor
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="spacer-sm"></div>
                    <div class="section-title">Note cliente</div>
                    <div class="box small-text" style="min-height: 28mm;">
                        <div class="paragraph-list">
                            @forelse($document['client_notes_paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <div class="writing-lines">
                                    @for($i = 0; $i < 3; $i++)
                                        <div></div>
                                    @endfor
                                </div>
                            @endforelse
                        </div>
                    </div>
                </td>

                <td style="width: 58%; vertical-align: top; padding-left: 3mm;">
                    <div class="section-title">Bozzetto approvato</div>
                    <table class="image-grid">
                        <tr>
                            <td style="width: {{ $backImage ? '50%' : '100%' }}; padding-right: {{ $backImage ? '2mm' : '0' }};">
                                <div class="image-frame image-frame--portrait" style="height: 144mm;">
                                    @if($frontImage)
                                        <img src="{{ $frontImage }}" alt="Disegno preventivo davanti">
                                    @else
                                        <div class="image-placeholder">Disegno non disponibile</div>
                                    @endif
                                </div>
                            </td>

                            @if($backImage)
                                <td style="width: 50%; padding-left: 2mm;">
                                    <div class="image-frame image-frame--portrait" style="height: 144mm;">
                                        <img src="{{ $backImage }}" alt="Disegno preventivo dietro">
                                    </div>
                                </td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            MadamaDore di Dora Maione - Scheda cliente abito su misura
        </div>
    </div>

    <div class="page-break"></div>
    <div class="document-page">
        <div class="page-title">Misure Cliente</div>
        <div class="page-subtitle">Misure standard e bozzetto collegati al preventivo</div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 40%; vertical-align: top; padding-right: 3mm;">
                    @include('pdf.partials.dress-measure-table', [
                        'title' => 'Misure',
                        'measurements' => $document['measurements'],
                        'customMeasurements' => $document['custom_measurements'],
                    ])

                    <div class="spacer-sm"></div>
                    <div class="section-title">Riepilogo capo</div>
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
                </td>
                <td style="width: 60%; vertical-align: top; padding-left: 3mm;">
                    <div class="section-title">Bozzetto approvato</div>
                    <table class="image-grid">
                        <tr>
                            <td style="width: {{ $backImage ? '50%' : '100%' }}; padding-right: {{ $backImage ? '2mm' : '0' }};">
                                <div class="image-frame image-frame--portrait" style="height: 154mm;">
                                    @if($frontImage)
                                        <img src="{{ $frontImage }}" alt="Disegno preventivo misure davanti">
                                    @else
                                        <div class="image-placeholder">Disegno non disponibile</div>
                                    @endif
                                </div>
                            </td>

                            @if($backImage)
                                <td style="width: 50%; padding-left: 2mm;">
                                    <div class="image-frame image-frame--portrait" style="height: 154mm;">
                                        <img src="{{ $backImage }}" alt="Disegno preventivo misure dietro">
                                    </div>
                                </td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>
    <div class="document-page">
        <div class="page-title">Condizioni Generali</div>
        <div class="page-subtitle">Riepilogo materiali, extra e condizioni economiche concordate</div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 58%; vertical-align: top; padding-right: 3mm;">
                    <div class="section-title">Tessuti</div>
                    <div class="box small-text">
                        @if(! empty($document['fabrics']))
                            <ul class="bullet-list">
                                @foreach($document['fabrics'] as $fabric)
                                    <li>
                                        {{ $fabric['name'] }}
                                        @if($fabric['type'] !== '')
                                            - {{ $fabric['type'] }}
                                        @endif
                                        @if($fabric['color_code'] !== '')
                                            - Codice {{ $fabric['color_code'] }}
                                        @endif
                                        - {{ $fabric['meters'] }} m
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="muted">Nessun tessuto inserito.</span>
                        @endif
                    </div>

                    <div class="spacer-sm"></div>
                    <div class="section-title">Extra e accessori</div>
                    <div class="box small-text">
                        @if(! empty($document['accessories']))
                            <ul class="bullet-list">
                                @foreach($document['accessories'] as $accessory)
                                    <li>{{ $accessory }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="muted">Nessun extra aggiuntivo.</span>
                        @endif
                    </div>

                    <div class="spacer-sm"></div>
                    <div class="section-title">Annotazioni generali</div>
                    <div class="box small-text" style="min-height: 36mm;">
                        <div class="paragraph-list">
                            @forelse($document['technical_description_paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <div class="writing-lines">
                                    @for($i = 0; $i < 4; $i++)
                                        <div></div>
                                    @endfor
                                </div>
                            @endforelse
                        </div>
                    </div>
                </td>

                <td style="width: 42%; vertical-align: top; padding-left: 3mm;">
                    <div class="section-title">Riepilogo economico</div>
                    <table class="grid-table small-text">
                        <tr>
                            <th>Costo totale</th>
                            <td style="text-align: right;">EUR {{ number_format($dress->total_client_price ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Acconto</th>
                            <td style="text-align: right;">EUR {{ number_format($dress->deposit ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Saldo finale</th>
                            <td style="text-align: right;">EUR {{ number_format($dress->remaining ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    </table>

                    <div class="spacer-sm"></div>
                    <div class="section-title">Campioni tessuto</div>
                    @for($i = 0; $i < 3; $i++)
                        @php($sample = $document['fabric_samples'][$i] ?? null)
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 3mm;">
                            <tr>
                                <td style="width: 42%; vertical-align: top; padding-right: 2mm;">
                                    <div class="sample-image">
                                        @if($sample && $sample['photo_absolute_path'])
                                            <img src="{{ $sample['photo_absolute_path'] }}" alt="Campione {{ $sample['name'] }}">
                                        @endif
                                    </div>
                                </td>
                                <td style="width: 58%; vertical-align: top;" class="small-text">
                                    <strong>Tessuto {{ $i + 1 }}</strong><br>
                                    {{ $sample['summary'] ?? '' }}
                                </td>
                            </tr>
                        </table>
                    @endfor

                    <div class="spacer-sm"></div>
                    <div class="section-title">Tabella acconti</div>
                    <table class="grid-table small-text">
                        <tr>
                            <th style="width: 40%;">Acconto</th>
                            <th>Data</th>
                        </tr>
                        @for($i = 0; $i < 4; $i++)
                            <tr>
                                <td style="height: 10mm;"></td>
                                <td></td>
                            </tr>
                        @endfor
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>
    <div class="document-page">
        <div class="page-title">Contratto di Vendita</div>
        <div class="page-subtitle">Condizioni contrattuali del capo su misura</div>

        <div class="box small-text" style="min-height: 188mm;">
            <p><strong>Art. 1 - Oggetto</strong><br>
                Il presente contratto disciplina la realizzazione e vendita di capi su misura e/o accessori prodotti da MadamaDore di Dora Maione
                (di seguito "Fornitore"), in favore del Cliente (di seguito "Acquirente"), sulla base del bozzetto, del preventivo e delle misure
                approvate dalle parti.
            </p>

            <p><strong>Art. 2 - Collegamento con preventivo e disegno</strong><br>
                Il presente contratto e il Preventivo n. {{ $dress->id }} / anno {{ now()->year }} costituiscono un unico documento di riferimento.
                Il disegno approvato e le misure rilevate fanno parte integrante dell'accordo.
            </p>

            <p><strong>Art. 3 - Condizioni di pagamento</strong><br>
                Alla firma viene versato l'acconto concordato. Eventuali pagamenti successivi possono essere distribuiti durante le prove.
                Il saldo finale e dovuto alla consegna del capo.
            </p>

            <p><strong>Art. 4 - Modifiche ed extra</strong><br>
                Qualsiasi modifica non tecnica richiesta successivamente all'approvazione iniziale puo comportare costi aggiuntivi e tempi ulteriori,
                previo accordo scritto tra le parti.
            </p>

            <p><strong>Art. 5 - Obblighi del Fornitore</strong><br>
                Il Fornitore si impegna a realizzare il capo con professionalita, secondo materiali, misure e bozzetto condivisi.
            </p>

            <p><strong>Art. 6 - Obblighi dell'Acquirente</strong><br>
                L'Acquirente si impegna a fornire dati corretti, rispettare gli appuntamenti per le prove e adempiere ai pagamenti concordati.
            </p>

            <p><strong>Art. 7 - Tutela del design</strong><br>
                Disegni, bozzetti e soluzioni stilistiche realizzati da MadamaDore restano di proprieta intellettuale del Fornitore,
                salvo diverso accordo scritto.
            </p>

            <p><strong>Art. 8 - Privacy</strong><br>
                I dati personali del Cliente sono trattati esclusivamente per finalita organizzative, amministrative e contrattuali
                in conformita alla normativa vigente.
            </p>

            <p><strong>Art. 9 - Recesso</strong><br>
                In caso di recesso dopo l'avvio della lavorazione, gli importi gia versati restano acquisiti a copertura delle attivita svolte,
                salvo diversi accordi formalizzati.
            </p>

            <p><strong>Art. 10 - Foro competente</strong><br>
                Per eventuali controversie e competente il foro del luogo in cui ha sede il Fornitore.
            </p>
        </div>

        <div class="spacer-sm"></div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 2mm;">
                    <div class="box small-text" style="min-height: 18mm;">
                        Luogo e data:<br><br>
                        ______________________________________
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 2mm;">
                    <div class="box small-text" style="min-height: 18mm;">
                        Preventivo di riferimento: {{ $dress->id }}/{{ now()->year }}<br><br>
                        Consegna prevista: {{ $dress->delivery_date?->format('d/m/Y') ?: '-' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="spacer-sm"></div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 2mm;">
                    <div class="box small-text" style="min-height: 20mm;">
                        Firma dell'Acquirente<br><br><br>
                        _______________________________
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 2mm;">
                    <div class="box small-text" style="min-height: 20mm;">
                        Firma del Fornitore<br><br><br>
                        _______________________________
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection
