<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Contratto Abito Speciale #{{ $dress->id }}</title>
    <style>
        @page { margin: 10mm; size: A4; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header { text-align: center; margin-bottom: 30px; margin-top: 20px; }
        .header .logo { height: 350px; display: block; margin: 0 auto 20px auto; }
        .page-title { text-align: center; font-size: 32px; font-weight: normal; margin: 40px 0 50px 0; color: #333; }
        .form-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .form-table td { border: 1px solid #333; padding: 15px; font-size: 14px; vertical-align: top; }
        .form-table td:first-child { background-color: white; width: 40%; }
        .info-bar { position: absolute; bottom: 15mm; left: 10mm; right: 10mm; text-align: center; font-size: 10px; color: #999; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <!-- PAGINA 1 -->
    <div class="header">
        <img src="{{ public_path('storage/branding/logo-madamadore.png') }}" alt="MadamaDorè di Dora Maione" class="logo">
    </div>

    <div class="page-title">Scheda Cliente (Abito Speciale)</div>

    <table class="form-table">
        <tr><td>PREVENTIVO NR.</td><td>{{ $dress->id }}</td></tr>
        <tr><td>Nome e Cognome</td><td>{{ $dress->customer_name }}</td></tr>
        <tr><td>Recapito Telefonico</td><td>{{ $dress->phone_number }}</td></tr>
        <tr><td>Descrizione Abito</td><td>{{ $dress->description ?? '' }}</td></tr>
        <tr><td>Data Consegna</td><td>{{ $dress->delivery_date?->format('d/m/Y') }}</td></tr>
        <tr><td>NOTE</td><td style="height: 80px;">{{ $dress->notes ?? '' }}</td></tr>
    </table>

    <div class="info-bar">
        MadamaDorè di Dora Maione - Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277
    </div>

    <!-- PAGINA 2 -->
    <div class="page-break">
        <table style="width:100%; border-collapse:collapse; margin-top:15px;">
            <tr>
                <td style="width:30%; vertical-align:top; padding-right:12px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td colspan="2" style="border:1px solid #333; padding:5px; background-color:#f5f5f5; font-weight:bold; text-align:center; font-size:11px;">MISURE</td>
                        </tr>
                        @if($dress->measurements)
                            @foreach(\App\Models\DressMeasurement::ORDERED_MEASURES as $field => $label)
                                <tr>
                                    <td style="border:1px solid #333; padding:2px 4px; font-size:8px;">{{ $label }}</td>
                                    <td style="border:1px solid #333; padding:2px; text-align:center; width:35px; font-size:8px;">{{ $dress->measurements->$field ?? '' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="2" style="border:1px solid #333; padding:6px; font-size:9px;">Nessuna misura disponibile</td></tr>
                        @endif
                    </table>
                </td>
                <td style="width:70%; vertical-align:top;">
                    <div style="border:1px solid #333; text-align:center; padding:12px; height:950px;">
                        <div style="border-bottom:1px solid #333; padding:8px; margin-bottom:10px; font-weight:bold; background-color:#f5f5f5; font-size:14px;">
                            Bozzetto / Disegno
                        </div>
                        <div style="padding-top:20px;">
                            @if($dress->final_image)
                                <img src="{{ storage_path('app/public/' . $dress->final_image) }}" alt="Abito Speciale Definitivo" style="max-width:98%; max-height:620px; border-radius:3px;">
                            @else
                                <div style="color:#999; font-style:italic; font-size:12px;">Immagine non disponibile</div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PAGINA 3 e 4: Condizioni generali e contratto -->
    {!! view('pdf.dress-contract', ['dress' => $dress])->render() !!}

</body>
</html>
