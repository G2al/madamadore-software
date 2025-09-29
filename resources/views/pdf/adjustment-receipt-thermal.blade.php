<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ricevuta #{{ $adjustment->id }}</title>
    <style>
        /* Pagina stretta per termica: 72 mm di larghezza, altezza flessibile */
        @page { size: 72mm auto; margin: 3mm 3mm 4mm 3mm; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.25;
            color: #000;
            margin: 0;
        }

        .center { text-align: center; }
        .mb-2 { margin-bottom: 2mm; }
        .mb-3 { margin-bottom: 3mm; }
        .small { font-size: 9px; }

        .title { font-weight: 700; font-size: 12px; }

        .hr { border-top: 1px dashed #000; margin: 2mm 0; }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        .kv td { padding: 0.8mm 0; }
        .kv td:first-child { width: 27mm; font-weight: 600; }

        .th, .tr { border-bottom: 1px dashed #000; }
        .th td { font-weight: 700; padding: 1mm 0; }
        .tr td { padding: 1mm 0; }

        .tot td { padding: 0.8mm 0; }
        .tot td:first-child { width: 26mm; }
        .tot td:last-child { text-align: right; }

        .note { margin-top: 3mm; }
    </style>
</head>
<body>

    <div class="center mb-2">
        <div class="title">Madamadorè</div>
        <div class="small">Ricevuta Aggiusto</div>
    </div>

    <div class="hr"></div>

<table class="kv mb-2">
    <tr>
        <td>ID Aggiusto</td>
        <td>#{{ $adjustment->id }}</td>
    </tr>
    <tr>
        <td>Data</td>
        <td>{{ $adjustment->created_at->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td>Cliente</td>
        <td>{{ $adjustment->customer->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Telefono</td>
        <td>{{ $adjustment->customer->phone_number ?? '-' }}</td>
    </tr>
    @if($adjustment->referente)
    <tr>
        <td>Referente</td>
        <td>{{ $adjustment->referente }}</td>
    </tr>
    @endif
</table>

    @if($adjustment->items && $adjustment->items->count())
        <div class="hr"></div>

        <table class="mb-2">
            <tr class="th">
                <td style="width: 28mm;">Aggiusto</td>
                <td>Descrizione</td>
            </tr>
            @foreach($adjustment->items as $item)
                <tr class="tr">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->description ?: '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="hr"></div>

    <table class="tot">
        <tr>
            <td>Prezzo Cliente</td>
            <td>€ {{ number_format($adjustment->client_price, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Acconto</td>
            <td>€ {{ number_format($adjustment->deposit, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Totale</td>
            <td>€ {{ number_format($adjustment->total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Rimanente</td>
            <td>€ {{ number_format($adjustment->remaining, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="hr"></div>

    <div class="center small note">
        Grazie! Documento generato automaticamente.
    </div>

</body>
</html>
