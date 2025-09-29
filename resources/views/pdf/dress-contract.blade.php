<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Contratto Abito #{{ $dress->id }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 13px;
            color: #333; 
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            margin-top: 20px;
        }
        .header .logo {
            height: 350px;
            display: block;
            margin: 0 auto 20px auto;
        }
        .page-title {
            text-align: center;
            font-size: 32px;
            font-weight: normal;
            margin: 40px 0 50px 0;
            color: #333;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .form-table td {
            border: 1px solid #333;
            padding: 15px;
            font-size: 14px;
            vertical-align: top;
        }
        .form-table td:first-child {
            font-weight: normal;
            background-color: white;
            width: 40%;
        }
        .info-bar {
            position: absolute;
            bottom: 15mm;
            left: 10mm;
            right: 10mm;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- PAGINA 1: Scheda Cliente -->
    <div class="header">
        <img src="{{ public_path('storage/branding/logo-madamadore.png') }}" alt="MadamaDorè di Dora Maione" class="logo">
    </div>

    <div class="page-title">Scheda Cliente</div>

    <table class="form-table">
        <tr>
            <td>PREVENTIVO NR.</td>
            <td>{{ $dress->id }}</td>
        </tr>
        <tr>
            <td>Nome e Cognome</td>
            <td>{{ $dress->customer_name }}</td>
        </tr>
        <tr>
            <td>Recapito Telefonico</td>
            <td>{{ $dress->phone_number }}</td>
        </tr>
        <tr>
            <td>Descrizione Abito</td>
            <td>{{ $dress->description ?? '' }}</td>
        </tr>
        <tr>
            <td>Data Consegna</td>
            <td>{{ $dress->delivery_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>NOTE</td>
            <td style="height: 80px;">{{ $dress->notes ?? '' }}</td>
        </tr>
    </table>

    <div class="info-bar">
        MadamaDorè di Dora Maione - Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277
    </div>

    <!-- PAGINA 2: Misure e Bozzetto -->
    <div class="page-break">
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <!-- Colonna Sinistra - Misure (30%) -->
                <td style="width: 30%; vertical-align: top; padding-right: 12px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td colspan="2" style="border: 1px solid #333; padding: 5px; background-color: #f5f5f5; font-weight: bold; text-align: center; font-size: 11px;">
                                MISURE
                            </td>
                        </tr>
                        @if($dress->measurements)
@foreach(\App\Models\DressMeasurement::ORDERED_MEASURES as $field => $label)
    <tr>
        <td style="border: 1px solid #333; padding: 2px 4px; font-size: 8px;">{{ $label }}</td>
        <td style="border: 1px solid #333; padding: 2px; text-align: center; width: 35px; font-size: 8px;">
            {{ $dress->measurements->$field ?? '' }}
        </td>
    </tr>
@endforeach

                        @else
                            <tr>
                                <td colspan="2" style="border: 1px solid #333; padding: 6px; font-size: 9px;">Nessuna misura disponibile</td>
                            </tr>
                        @endif

                        <!-- Misure Personalizzate -->
                        @if($dress->customMeasurements && $dress->customMeasurements->count() > 0)
                            <tr>
                                <td colspan="2" style="border: 1px solid #333; padding: 5px; background-color: #f0f0f0; font-weight: bold; text-align: center; font-size: 10px;">
                                    MISURE PERSONALIZZATE
                                </td>
                            </tr>
@foreach($dress->customMeasurements as $customMeasurement)
    <tr>
        <td style="border: 1px solid #333; padding: 2px 4px; font-size: 8px;">{{ $customMeasurement->label }}</td>
        <td style="border: 1px solid #333; padding: 2px; text-align: center; width: 35px; font-size: 8px;">
            @if($customMeasurement->value)
                {{ $customMeasurement->value }} {{ $customMeasurement->unit }}
            @else
                -
            @endif
        </td>
    </tr>
@endforeach
                        @endif
                    </table>
                </td>
                
                <!-- Colonna Destra - Bozzetto/Disegno (70%) -->
                <td style="width: 70%; vertical-align: top;">
                    <div style="border: 1px solid #333; text-align: center; padding: 12px; height: 950px;">
                        <div style="border-bottom: 1px solid #333; padding: 8px; margin-bottom: 10px; font-weight: bold; background-color: #f5f5f5; font-size: 14px;">
                            Bozzetto / Disegno
                        </div>
                        <div style="padding-top: 20px;">
                            @if($dress->final_image)
                                <img src="{{ storage_path('app/public/' . $dress->final_image) }}" alt="Abito Definitivo" 
                                     style="max-width: 98%; max-height: 620px; border-radius: 3px;">
                            @else
                                <div style="color: #999; font-style: italic; font-size: 12px;">
                                    Immagine non disponibile
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PAGINA 3: Condizioni Generali -->
    <div class="page-break">
        <h2 style="text-align: center; font-size: 16px; margin: 20px 0;">CONDIZIONI GENERALI</h2>
        <p style="text-align: center; font-size: 12px; margin-bottom: 15px;">Atelier MadamaDorè di Dora Maione</p>
        <p style="font-size: 12px; margin-bottom: 20px;">La sottoscrizione del presente costituisce parte integrante del contratto di vendita e delle complessive 4 pagine.</p>

        <!-- Descrizione Capo, Stoffe e Accessori -->
        <h3 style="font-size: 14px; margin: 12px 0 8px 0;">❖ Descrizione Capo, Stoffe e Accessori</h3>
        <div style="font-size: 12px; line-height: 1.4; margin-bottom: 15px;">
            @if($dress->fabrics && $dress->fabrics->count() > 0)
                <strong>TESSUTI:</strong><br>
                @foreach($dress->fabrics as $fabric)
                    • {{ $fabric->name ?? 'N/A' }} - {{ $fabric->type ?? 'N/A' }} 
                    ({{ $fabric->meters ?? 0 }}mt, {{ $fabric->supplier ?? 'N/A' }})
                    @if($fabric->color_code) - Codice: {{ $fabric->color_code }} @endif<br>
                @endforeach
                <br>
            @endif
            
            @if($dress->extras && $dress->extras->count() > 0)
                <strong>EXTRA:</strong><br>
                @foreach($dress->extras as $extra)
                    • {{ $extra->description ?? 'N/A' }} (€{{ number_format($extra->cost ?? 0, 2) }})<br>
                @endforeach
            @endif
        </div>

        <!-- Annotazioni Generali -->
        <h3 style="font-size: 14px; margin: 12px 0 8px 0;">❖ Annotazioni Generali</h3>
        <div style="font-size: 12px; line-height: 1.4; margin-bottom: 20px;">
            {{ $dress->notes ?? 'Nessuna annotazione' }}
        </div>

        <!-- Riepilogo Economico -->
        <div style="text-align: center; margin: 25px 0;">
            <strong style="font-size: 14px;">Riepilogo Economico:</strong>
            <table style="margin: 15px auto; border-collapse: collapse; width: 350px; font-size: 12px;">
                <tr>
                    <td style="border: 1px solid #333; padding: 8px; background-color: #f5f5f5; font-weight: bold;">Costo Totale</td>
                    <td style="border: 1px solid #333; padding: 8px; text-align: right;">€ {{ number_format($dress->total_client_price ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #333; padding: 8px; background-color: #f5f5f5; font-weight: bold;">Acconto 30%</td>
                    <td style="border: 1px solid #333; padding: 8px; text-align: right;">€ {{ number_format($dress->deposit ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #333; padding: 8px; background-color: #f5f5f5; font-weight: bold;">Saldo Finale</td>
                    <td style="border: 1px solid #333; padding: 8px; text-align: right;">€ {{ number_format($dress->remaining ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Tabella Acconti -->
        <div style="text-align: center; margin: 25px 0;">
            <strong style="font-size: 14px;">Tabella Acconti:</strong>
            <table style="margin: 15px auto; border-collapse: collapse; width: 300px; font-size: 12px;">
                <tr>
                    <th style="border: 1px solid #333; padding: 8px; background-color: #f5f5f5; font-weight: bold;">Acconto</th>
                    <th style="border: 1px solid #333; padding: 8px; background-color: #f5f5f5; font-weight: bold;">Data</th>
                </tr>
                @for($i=0; $i<5; $i++)
                <tr>
                    <td style="border: 1px solid #333; padding: 14px; text-align: center;"></td>
                    <td style="border: 1px solid #333; padding: 14px; text-align: center;"></td>
                </tr>
                @endfor
            </table>
        </div>
    </div>

    <!-- PAGINA 4: Contratto di Vendita -->
    <div class="page-break">
        <h2 style="text-align: center; font-size: 16px; margin: 20px 0;">CONTRATTO DI VENDITA</h2>
        <p style="text-align: center; font-size: 12px; margin-bottom: 15px;">Atelier MadamaDorè di Dora Maione</p>

        <div style="font-size: 12px; line-height: 1.4; text-align: justify;">
            <p><strong>Art. 1 – Oggetto</strong><br>
            Il presente contratto disciplina la realizzazione e vendita di capi su misura e/o accessori prodotti da MadamaDorè di Dora Maione (di seguito "Fornitore"), in favore del Cliente (di seguito "Acquirente"), sulla base del bozzetto/disegno e preventivo concordato e sottoscritto dalle parti.</p>

            <p><strong>Art. 2 – Collegamento con bozza di disegno/preventivo</strong><br>
            1. Il presente contratto è inscindibilmente legato al Preventivo n. {{ $dress->id }} / anno {{ now()->year }}.<br>
            2. Il Preventivo firmato costituisce parte integrante e sostanziale del contratto.<br>
            3. In caso di divergenze prevale quanto indicato nel Preventivo e nel disegno approvato.</p>

            <p><strong>Art. 3 – Condizioni di pagamento</strong><br>
            1. All'atto della firma il Cliente versa un acconto pari al 30% del prezzo.<br>
            2. I pagamenti successivi saranno dilazionati durante le prove.<br>
            3. Il saldo finale è dovuto alla consegna.<br>
            4. In caso di mancato pagamento il Fornitore può sospendere la consegna e trattenere quanto versato.</p>

            <p><strong>Art. 4 – Modifiche ed Extra</strong><br>
            Qualsiasi modifica non tecnica è considerata extra e comporta costi aggiuntivi, solo previo accordo scritto firmato dal Cliente.</p>

            <p><strong>Art. 5 – Obblighi del Fornitore</strong><br>
            Realizzare il capo secondo misure, materiali e disegno concordati con professionalità e qualità.</p>

            <p><strong>Art. 6 – Obblighi dell'Acquirente</strong><br>
            Rispettare i pagamenti, presentarsi puntuale alle prove, firmare eventuali variazioni, fornire dati corretti.</p>

            <p><strong>Art. 7 – Tutela del design</strong><br>
            Tutti i disegni e capi realizzati sono proprietà intellettuale del Fornitore. Vietata la riproduzione senza autorizzazione.</p>

            <p><strong>Art. 8 – Privacy</strong><br>
            I dati personali sono trattati solo per finalità contrattuali in conformità al GDPR.</p>

            <p><strong>Art. 9 – Recesso</strong><br>
            In caso di recesso del Cliente dopo la firma, gli importi versati restano acquisiti. In caso di inadempimento grave del Fornitore, il Cliente ha diritto alla restituzione delle somme.</p>

            <p><strong>Art. 10 – Foro competente</strong><br>
            Foro esclusivo del luogo di sede legale del Fornitore.</p>
        </div>

        <!-- Firme -->
        <div style="margin-top: 25px; font-size: 12px;">
            <p>Letto, approvato e sottoscritto in ogni parte.</p>
            <p style="margin-top: 15px;">Luogo e data: ______________________________________</p>
            <p style="margin-top: 15px;">Firma dell'Acquirente _______________________________</p>
            <p style="margin-top: 15px;">Firma del Fornitore _________________________________</p>
        </div>
    </div>
</body>
</html>