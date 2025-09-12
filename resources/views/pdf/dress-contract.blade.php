<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Contratto Abito #{{ $dress->id }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #333; 
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 { 
            margin: 5px 0; 
            font-size: 24px; 
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        .page-title {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            margin: 30px 0;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .form-table td {
            border: 1px solid #333;
            padding: 8px;
            vertical-align: top;
        }
        .form-table td:first-child {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 30%;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- PAGINA 1: Scheda Cliente -->
    <div class="header">
        <h1>MadamaDorè di Dora Maione</h1>
        <p>Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277</p>
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
            <td>{{ $dress->notes }}</td>
        </tr>
        <tr>
            <td>Data Consegna</td>
            <td>{{ $dress->delivery_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>NOTE</td>
            <td>{{ $dress->notes }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- PAGINA 2: Misure -->
<div class="header">
    <h1>MadamaDorè di Dora Maione</h1>
    <p>Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277</p>
</div>

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <tr>
        <!-- Colonna Sinistra - Misure (45%) -->
        <td style="width: 45%; vertical-align: top; padding-right: 15px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="border: 1px solid #333; padding: 8px; background-color: #f5f5f5; font-weight: bold; text-align: center;" colspan="2">
                        MISURE
                    </td>
                </tr>
                @if($dress->measurements)
                    @foreach(\App\Models\DressMeasurement::ORDERED_MEASURES as $field => $label)
                        <tr>
                            <td style="border: 1px solid #333; padding: 4px; font-size: 9px;">{{ $label }}</td>
                            <td style="border: 1px solid #333; padding: 4px; text-align: center; width: 50px; font-size: 9px;">
                                {{ $dress->measurements->$field ?? '' }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px;" colspan="2">Nessuna misura disponibile</td>
                    </tr>
                @endif
            </table>
        </td>
        
        <!-- Colonna Destra - Bozzetto/Disegno (55%) -->
        <td style="width: 55%; vertical-align: top;">
            <div style="border: 1px solid #333; text-align: center; padding: 10px; height: 500px;">
                <div style="border-bottom: 1px solid #333; padding: 8px; margin-bottom: 10px; font-weight: bold; background-color: #f5f5f5;">
                    Bozzetto / Disegno:
                </div>
                @if($dress->final_image)
                    <img src="{{ storage_path('app/public/' . $dress->final_image) }}" alt="Abito Definitivo" 
                         style="max-width: 100%; max-height: 450px; border-radius: 3px;">
                @else
                    <div style="padding: 100px 20px; color: #999; font-style: italic;">
                        Immagine non disponibile
                    </div>
                @endif
            </div>
        </td>
    </tr>
</table>

<div class="page-break"></div>

<!-- PAGINA 3: Condizioni Generali -->
<div class="header">
    <h1>MadamaDorè di Dora Maione</h1>
    <p>Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277</p>
</div>

<h2 style="text-align: center; font-size: 14px; margin: 20px 0;">CONDIZIONI GENERALI</h2>
<p style="text-align: center; font-size: 10px; margin-bottom: 15px;">Atelier MadamaDorè di Dora Maione</p>
<p style="font-size: 9px; margin-bottom: 20px;">La sottoscrizione del presente costituisce parte integrante del contratto di vendita e delle complessive 4 pagine.</p>

<!-- Descrizione Capo, Stoffe e Accessori -->
<h3 style="font-size: 11px; margin: 15px 0 8px 0;">❖ Descrizione Capo, Stoffe e Accessori</h3>
<div style="font-size: 9px; line-height: 1.4; margin-bottom: 15px;">
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
<h3 style="font-size: 11px; margin: 15px 0 8px 0;">❖ Annotazioni Generali</h3>
<div style="font-size: 9px; line-height: 1.4; margin-bottom: 20px;">
    {{ $dress->notes ?? 'Nessuna annotazione' }}
</div>

<!-- Riepilogo Economico -->
<div style="text-align: center; margin: 30px 0;">
    <strong style="font-size: 12px;">Riepilogo Economico:</strong>
    <table style="margin: 15px auto; border-collapse: collapse; width: 300px;">
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

<!-- Firme -->
<div style="margin-top: 40px; font-size: 10px;">
    <p>Letto, approvato e sottoscritto in ogni parte.</p>
    <br><br>
    <p>Luogo e data: ______________________________________</p>
    <br><br>
    <p>Firma dell'Acquirente _______________________________</p>
    <br><br>
    <p>Firma del Fornitore _________________________________</p>
</div>

<div class="page-break"></div>

<!-- PAGINA 4: Contratto di Vendita -->
<div class="header">
    <h1>MadamaDorè di Dora Maione</h1>
    <p>Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277</p>
</div>

<h2 style="text-align: center; font-size: 14px; margin: 20px 0;">CONTRATTO DI VENDITA</h2>
<p style="text-align: center; font-size: 10px; margin-bottom: 15px;">Atelier MadamaDorè di Dora Maione</p>

<div style="font-size: 8px; line-height: 1.3; text-align: justify;">
    <p><strong>Art. 1 – Oggetto</strong><br>
    Il presente contratto disciplina la realizzazione e vendita di capi su misura e/o accessori prodotti da MadamaDorè di Dora Maione (di seguito "Fornitore"), in favore del Cliente (di seguito "Acquirente"), sulla base del bozzetto/disegno e preventivo concordato e sottoscritto dalle parti.</p>

    <p><strong>Art. 2 – Collegamento con bozza di disegno/preventivo</strong><br>
    1. Il presente contratto è inscindibilmente legato al Preventivo n. {{ $dress->id }} / anno {{ now()->year }} (di seguito "Preventivo"), contenente disegno, misure, materiali e annotazioni specifiche.<br>
    2. Il Preventivo, debitamente firmato dall'Acquirente, costituisce parte integrante e sostanziale del presente contratto.<br>
    3. Eventuali divergenze interpretative saranno risolte dando prevalenza a quanto indicato nel Preventivo e nel disegno approvato.</p>

    <p><strong>Art. 3 – Condizioni di pagamento</strong><br>
    1. All'atto della sottoscrizione del presente contratto l'Acquirente si impegna a versare al Fornitore un acconto pari al 30% del prezzo concordato.<br>
    2. I pagamenti successivi saranno effettuati in forma dilazionata, secondo le scadenze fissate in occasione degli incontri di misura e delle prove.<br>
    3. Il saldo finale dovrà essere versato dall'Acquirente all'ultimo incontro di misura/consegna.<br>
    4. In caso di mancato pagamento, il Fornitore si riserva la facoltà di sospendere la consegna e trattenere quanto già versato a titolo di caparra confirmatoria.</p>

    <p><strong>Art. 4 – Modifiche ed Extra</strong><br>
    1. Qualsiasi modifica o aggiunta rispetto al bozzetto iniziale sottoscritto, che non sia di natura tecnica o di miglioria tecnica al capo, è da considerarsi extra e comporterà un costo aggiuntivo.<br>
    2. Gli extra saranno eseguiti solo previo accordo scritto e firmato dall'Acquirente al momento della richiesta.<br>
    3. Eventuali richieste tardive di modifiche estetiche, non riconducibili a difetti di conformità o esigenze tecniche, non sospendono i termini di pagamento.</p>

    <p><strong>Art. 5 – Obblighi del Fornitore</strong><br>
    Il Fornitore si impegna a realizzare il capo secondo le misure, i materiali e il disegno concordato, garantendo professionalità, qualità dei materiali e riservatezza nei confronti del Cliente.</p>

    <p><strong>Art. 6 – Obblighi dell'Acquirente</strong><br>
    L'Acquirente si impegna a:<br>
    - rispettare le scadenze di pagamento pattuite;<br>
    - presentarsi puntualmente agli appuntamenti di misura e prova;<br>
    - approvare con firma eventuali variazioni o extra richiesti;<br>
    - fornire dati corretti e veritieri per la gestione amministrativa e contrattuale.</p>

    <p><strong>Art. 7 – Tutela del design e divieto di riproduzione</strong><br>
    1. Tutti i bozzetti, disegni, modelli e capi realizzati da MadamaDorè sono opere di proprietà intellettuale del Fornitore.<br>
    2. È fatto divieto all'Acquirente di riprodurre, copiare o far replicare da terzi i capi e i disegni forniti, pena l'azione legale per violazione dei diritti di proprietà intellettuale.<br>
    3. Qualsiasi utilizzo diverso da quello personale (es. commerciale, promozionale, pubblicitario) deve essere previamente autorizzato per iscritto dal Fornitore.</p>

    <p><strong>Art. 8 – Privacy e trattamento dei dati personali</strong><br>
    1. I dati personali dell'Acquirente saranno trattati dal Fornitore esclusivamente per finalità contrattuali, amministrative e fiscali, in conformità al Regolamento UE 2016/679 (GDPR).<br>
    2. L'Acquirente potrà in qualsiasi momento esercitare i diritti di accesso, rettifica e cancellazione dei propri dati contattando il Fornitore.</p>

    <p><strong>Art. 9 – Recesso e risoluzione</strong><br>
    1. In caso di recesso dell'Acquirente successivo alla firma, gli importi versati resteranno acquisiti dal Fornitore a titolo di caparra confirmatoria e copertura spese già sostenute.<br>
    2. In caso di inadempimento grave del Fornitore, l'Acquirente potrà recedere con diritto alla restituzione delle somme versate.</p>

    <p><strong>Art. 10 – Foro competente</strong><br>
    Per ogni controversia relativa alla validità, interpretazione ed esecuzione del presente contratto sarà competente in via esclusiva il Foro del luogo in cui ha sede legale il Fornitore.</p>
</div>
</body>
</html>