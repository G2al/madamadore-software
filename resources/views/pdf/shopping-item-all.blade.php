<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Lista della Spesa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { text-align: center; font-size: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f3f3f3; font-weight: bold; }
        td img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
        .right { text-align: right; }
        .footer { text-align: center; margin-top: 25px; font-size: 11px; color: #888; }
    </style>
</head>
<body>
    <h1>Lista della Spesa</h1>

    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nome</th>
                <th>Quantita</th>
                <th>Misure</th>
                <th>Fornitore</th>
                <th>Prezzo (€)</th>
                <th>Data Acquisto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $unitLabel = match($item->unit_type) {
                        'metri' => 'mt',
                        'pezzi' => 'pz',
                        default => $item->unit_type,
                    };
                    $photoPath = $item->photo_path
                        ? storage_path('app/public/' . $item->photo_path)
                        : null;
                @endphp
                <tr>
                    <td>
                        @if($photoPath && file_exists($photoPath))
                            <img src="{{ $photoPath }}" alt="Foto">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->name }}</td>
                    <td>
                        {{ is_null($item->quantity) ? '-' : number_format((float) $item->quantity, 2, ',', '.') }}
                    </td>
                    <td>{{ $unitLabel ?? '-' }}</td>
                    <td>{{ $item->supplier ?? '-' }}</td>
                    <td class="right">€ {{ number_format($item->price, 2, ',', '.') }}</td>
                    <td>
                        {{ $item->purchase_date 
                            ? \Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') 
                            : 'Non saldato' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Totale articoli: {{ $items->count() }}<br>
        Generato automaticamente dal gestionale — {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
