<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            margin: 0 0 10px;
        }

        h2 {
            text-align: center;
            font-size: 14px;
            margin: 0 0 18px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f3f3;
            font-weight: bold;
        }

        td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .supplier-title {
            margin-bottom: 16px;
        }

        .supplier-title .supplier-name {
            font-size: 16px;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 11px;
            color: #888;
        }

        .muted {
            color: #888;
        }
    </style>
</head>
<body>
    @forelse($supplierGroups as $supplierGroup)
        <div class="@if(! $loop->first) page-break @endif">
            <h1>Lista della Spesa</h1>
            <div class="supplier-title center">
                <div class="supplier-name">{{ $supplierGroup['name'] }}</div>
                <div>{{ $subtitle }}</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 9%;">Foto</th>
                        <th style="width: 29%;">Nome</th>
                        <th style="width: 11%;">Codice Colore</th>
                        <th style="width: 10%;">Quantità</th>
                        <th style="width: 8%;">Misure</th>
                        <th style="width: 13%;">Fornitore</th>
                        <th style="width: 10%;">Prezzo (€)</th>
                        <th style="width: 10%;">Data Acquisto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplierGroup['rows'] as $row)
                        @php
                            $photoPath = $row['photo_path']
                                ? storage_path('app/public/' . $row['photo_path'])
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
                            <td>
                                <strong>{{ $row['name'] }}</strong>
                                @if($row['subtitle'] !== '')
                                    <br><span class="muted">{{ $row['subtitle'] }}</span>
                                @endif
                            </td>
                            <td>{{ $row['variant'] ?: '-' }}</td>
                            <td class="right">
                                {{ $row['quantity'] === null ? '-' : number_format((float) $row['quantity'], 2, ',', '.') }}
                            </td>
                            <td>{{ $row['unit'] ?? '-' }}</td>
                            <td>{{ $supplierGroup['name'] }}</td>
                            <td class="right">
                                @if($row['price'] !== null)
                                    € {{ number_format((float) $row['price'], 2, ',', '.') }}
                                @else
                                    € 0,00
                                @endif
                            </td>
                            <td>{{ $row['purchase_label'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer">
                Totale articoli: {{ $supplierGroup['total_rows'] }}<br>
                @if(! empty($supplierGroup['unit_totals']))
                    @foreach($supplierGroup['unit_totals'] as $unitTotal)
                        {{ number_format($unitTotal['quantity'], 2, ',', '.') }} {{ $unitTotal['unit'] }}@if(! $loop->last) · @endif
                    @endforeach
                    <br>
                @endif
                Generato automaticamente dal gestionale — {{ $generatedAt }}
            </div>
        </div>
    @empty
        <h1>Lista della Spesa</h1>
        <div class="footer">Nessuna voce disponibile per la stampa.</div>
    @endforelse
</body>
</html>
