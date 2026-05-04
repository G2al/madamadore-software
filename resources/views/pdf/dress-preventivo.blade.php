@extends('pdf.layouts.dress-document', ['title' => 'Preventivo Abito #' . $dress->id])

@php
    $approvedFrontImage = $document['approved_front_image_path'] ?? $document['design_image_path'] ?? null;
    $approvedBackImage = $document['approved_back_image_path'] ?? null;
    $logoPath = file_exists(public_path('logo_madamadore_pdf.jpg'))
        ? public_path('logo_madamadore_pdf.jpg')
        : (file_exists(public_path('logo_madamadore.png')) ? public_path('logo_madamadore.png') : null);
    $finalSheetImage = null;

    foreach ([$dress->final_image ?? null, $dress->drawing_image ?? null, $dress->sketch_image ?? null] as $candidateImage) {
        if (blank($candidateImage)) {
            continue;
        }

        $absoluteCandidateImage = storage_path('app/public/' . ltrim((string) $candidateImage, '/'));

        if (file_exists($absoluteCandidateImage)) {
            $finalSheetImage = $absoluteCandidateImage;
            break;
        }
    }

    $secondPageImage = $finalSheetImage ?? $approvedFrontImage ?? $approvedBackImage;
@endphp

@section('content')
    <div class="document-page" style="border: 2px solid #111; padding: 0; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 54mm; left: 10%; width: 80%; text-align: center;">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="MadamaDore" style="width: 84mm; height: auto; display: block; margin: 0 auto;">
            @else
                <div style="font-size: 38px; font-weight: bold;">MadamaDore</div>
            @endif

            <div style="margin-top: 5mm; margin-bottom: 8mm; font-size: 15px; font-style: italic;">
                Scheda cliente
            </div>

            <table style="width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 13px; color: #222;">
                <tr>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; width: 50%; font-style: italic; vertical-align: middle;">Preventivo Nr.</td>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; width: 50%; vertical-align: middle;">{{ $dress->id }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; font-style: italic; vertical-align: middle;">Nome e cognome</td>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; vertical-align: middle;">{{ $dress->customer_name }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; font-style: italic; vertical-align: middle;">Telefono</td>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; vertical-align: middle;">{{ $dress->phone_number }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; font-style: italic; vertical-align: middle;">Data di consegna</td>
                    <td style="border: 1px solid #c6c6c6; padding: 6mm 4mm; vertical-align: middle;">{{ $dress->delivery_date?->format('d/m/Y') ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="page-break"></div>
    <div class="document-page" style="padding: 0; overflow: hidden; border: none;">
        <div style="height: 270mm; display: table; width: 100%;">
            <div style="display: table-cell; vertical-align: middle; text-align: center;">
                @if($secondPageImage)
                    <img src="{{ $secondPageImage }}" alt="Abito definitivo" style="display: block; width: 100%; height: auto; max-height: 270mm; margin: 0 auto;">
                @else
                    <div class="image-placeholder">Abito definitivo non disponibile</div>
                @endif
            </div>
        </div>
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

    <div class="page-break"></div>
    <div class="document-page">
        <div class="page-title">Riepilogo Economico</div>
        <div class="page-subtitle">Acconti, descrizione abito e riepilogo finale</div>

        <table style="width: 100%; border-collapse: collapse; height: 240mm; table-layout: fixed;">
            <tr>
                <td style="width: 58%; height: 120mm; vertical-align: top; padding-right: 3mm; padding-bottom: 2mm;">
                    <div class="section-title">Descrizione abito</div>
                    <div class="box small-text" style="height: 110mm; overflow: hidden;">
                        <div class="paragraph-list">
                            @forelse($document['description_paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <div class="writing-lines">
                                    @for($i = 0; $i < 9; $i++)
                                        <div></div>
                                    @endfor
                                </div>
                            @endforelse
                        </div>
                    </div>
                </td>

                <td style="width: 42%; height: 122mm; vertical-align: top; padding-left: 3mm; padding-bottom: 2mm;">
                    <div class="section-title">Riepilogo economico</div>
                    <table class="grid-table small-text" style="height: 110mm;">
                        <tr>
                            <th style="height: 34mm;">Costo totale</th>
                            <td style="text-align: right; height: 34mm;">EUR {{ number_format($dress->total_client_price ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th style="height: 34mm;">Acconto</th>
                            <td style="text-align: right; height: 34mm;">EUR {{ number_format($dress->deposit ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th style="height: 34mm;">Saldo finale</th>
                            <td style="text-align: right; height: 34mm;">EUR {{ number_format($dress->remaining ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td style="width: 58%; height: 120mm; vertical-align: top; padding-right: 3mm; padding-top: 2mm;">
                    <div class="section-title">Note cliente</div>
                    <div class="box small-text" style="height: 110mm; overflow: hidden;">
                        <div class="paragraph-list">
                            @forelse($document['client_notes_paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @empty
                                <div class="writing-lines">
                                    @for($i = 0; $i < 9; $i++)
                                        <div></div>
                                    @endfor
                                </div>
                            @endforelse
                        </div>
                    </div>
                </td>

                <td style="width: 42%; height: 120mm; vertical-align: top; padding-left: 3mm; padding-top: 2mm;">
                    <div class="section-title">Tabella acconti</div>
                    <table class="grid-table small-text" style="height: 110mm;">
                        <tr>
                            <th style="width: 40%;">Acconto</th>
                            <th>Data</th>
                        </tr>
                        @for($i = 0; $i < 6; $i++)
                            <tr>
                                <td style="height: 15.5mm;"></td>
                                <td></td>
                            </tr>
                        @endfor
                    </table>
                </td>
            </tr>
        </table>
    </div>
@endsection
