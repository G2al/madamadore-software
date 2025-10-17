<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ricevuta Singola - {{ $item->name }}</title>
    <style>
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

        .tot td { padding: 0.8mm 0; }
        .tot td:first-child { width: 26mm; }
        .tot td:last-child { text-align: right; font-weight: bold; }

        .note { margin-top: 3mm; }
    </style>
</head>
<body>

    <div class="center mb-2">
        <div class="title">Madamadorè</div>
        <div class="small">Ricevuta Aggiusto Singolo</div>
    </div>

    <div class="hr"></div>

    <table class="kv mb-2">
        <tr>
            <td>ID Aggiusto</td>
            <td>#{{ $adjustment->id }}-{{ $item->id }}</td>
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

    <div class="hr"></div>

    <table class="kv mb-3">
        <tr>
            <td><strong>Aggiusto</strong></td>
            <td><strong>{{ $item->name }}</strong></td>
        </tr>
        @if($item->description)
        <tr>
            <td>Descrizione</td>
            <td>{{ $item->description }}</td>
        </tr>
        @endif
    </table>

    @if($item->price && $item->price > 0)
    <div class="hr"></div>

    <table class="tot">
        <tr>
            <td>Prezzo</td>
            <td>€ {{ number_format($item->price, 2, ',', '.') }}</td>
        </tr>
    </table>
    @endif

    <div class="hr"></div>

    <div class="center small note">
        Data consegna: {{ $adjustment->delivery_date ? $adjustment->delivery_date->format('d/m/Y') : 'Da definire' }}<br>
        Stato: {{ \App\Models\CompanyAdjustment::getStatusLabels()[$adjustment->status] ?? $adjustment->status }}
    </div>

    <div class="hr"></div>

    <div class="center small note">
        Grazie per la fiducia!<br>
        Documento generato automaticamente
    </div>

</body>
</html>