<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Voce Spesa - {{ $item->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; margin: 25px; }
        h1 { text-align: center; font-size: 20px; margin-bottom: 20px; }
        .container { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .row { margin-bottom: 10px; }
        .label { font-weight: bold; width: 180px; display: inline-block; }
        .photo { text-align: center; margin-top: 20px; }
        img { max-width: 250px; max-height: 250px; border-radius: 8px; border: 1px solid #ddd; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <h1>Voce della Spesa</h1>

    <div class="container">
        <div class="row"><span class="label">Nome:</span> {{ $item->name }}</div>
        <div class="row"><span class="label">Prezzo:</span> € {{ number_format($item->price, 2, ',', '.') }}</div>
        <div class="row"><span class="label">Quantità:</span> {{ $item->quantity }}</div>
        <div class="row"><span class="label">Misure:</span> {{ $item->measure ?? '-' }}</div>
        <div class="row"><span class="label">Fornitore:</span> {{ $item->supplier ?? '-' }}</div>
        <div class="row"><span class="label">Data Acquisto:</span> 
            {{ $item->purchase_date ? \Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') : 'Non ancora saldato' }}
        </div>

        @if($item->photo)
            <div class="photo">
                <img src="{{ public_path('storage/' . $item->photo) }}" alt="Foto Prodotto">
            </div>
        @endif
    </div>

    <div class="footer">
        Generato automaticamente dal gestionale — {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
