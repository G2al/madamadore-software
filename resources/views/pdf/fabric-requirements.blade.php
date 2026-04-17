<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista Acquisti Tessuti</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .supplier-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .supplier-header {
            background: #34495e;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .fabric-group {
            margin-bottom: 18px;
            border: 1px solid #ddd;
        }
        .fabric-header {
            background: #ecf0f1;
            padding: 8px 12px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
        }
        .fabric-type {
            font-size: 10px;
            font-weight: normal;
            color: #5d6d7e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
        }
        .text-right { text-align: right; }
        .total-row {
            background: #e8f5e8;
            font-weight: bold;
            border-top: 2px solid #27ae60;
        }
        .price { color: #e74c3c; font-weight: bold; }
        .meters { color: #27ae60; font-weight: bold; }
        .muted { color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        @if (! empty($colorCode))
            <h1>LISTA ACQUISTI - CODICE COLORE {{ strtoupper($colorCode) }}</h1>
        @else
            <h1>LISTA ACQUISTI TESSUTI</h1>
        @endif
        <p><strong>Generata il:</strong> {{ $generatedAt }}</p>
        <p class="price"><strong>TOTALE COMPLESSIVO: EUR {{ number_format($totalCost, 2, ',', '.') }}</strong></p>
    </div>

    @php
        $fabricsBySupplier = $fabrics->groupBy(fn ($fabric) => $fabric->supplier ?: 'Senza fornitore');
    @endphp

    @foreach($fabricsBySupplier as $supplier => $supplierItems)
        @php
            $supplierTotalMeters = $supplierItems->sum('meters');
            $supplierTotalCost = $supplierItems->sum(fn ($item) => (float) $item->meters * (float) $item->purchase_price);
            $fabricsByName = $supplierItems->groupBy(fn ($item) => $item->name ?: 'Tessuto senza nome');
        @endphp

        <div class="supplier-section">
            <div class="supplier-header">
                FORNITORE: {{ strtoupper($supplier) }}
                <span style="float: right;">
                    Totale: {{ number_format((float) $supplierTotalMeters, 2, ',', '.') }} mt - EUR {{ number_format((float) $supplierTotalCost, 2, ',', '.') }}
                </span>
            </div>

            @foreach($fabricsByName as $fabricName => $fabricItems)
                @php
                    $fabricTotalMeters = $fabricItems->sum('meters');
                    $fabricTotalCost = $fabricItems->sum(fn ($item) => (float) $item->meters * (float) $item->purchase_price);
                    $fabricTypes = $fabricItems->pluck('type')->filter()->unique()->values();
                    $fabricColors = $fabricItems->groupBy(fn ($item) => $item->color_code ?: 'Senza codice');
                @endphp

                <div class="fabric-group">
                    <div class="fabric-header">
                        TESSUTO: {{ strtoupper($fabricName) }}
                        @if($fabricTypes->isNotEmpty())
                            <span class="fabric-type">({{ $fabricTypes->join(', ') }})</span>
                        @endif
                        <span style="float: right;">
                            Totale: {{ number_format((float) $fabricTotalMeters, 2, ',', '.') }} mt - EUR {{ number_format((float) $fabricTotalCost, 2, ',', '.') }}
                        </span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 34%;">CODICE COLORE</th>
                                <th style="width: 22%;">METRI</th>
                                <th style="width: 22%;">PREZZO/MT</th>
                                <th style="width: 22%;">SUBTOTALE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fabricColors as $groupColorCode => $colorItems)
                                @php
                                    $colorTotalMeters = $colorItems->sum('meters');
                                    $colorTotalCost = $colorItems->sum(fn ($item) => (float) $item->meters * (float) $item->purchase_price);
                                    $uniquePrices = $colorItems
                                        ->pluck('purchase_price')
                                        ->filter(fn ($price) => $price !== null)
                                        ->map(fn ($price) => (float) $price)
                                        ->unique()
                                        ->values();

                                    $priceLabel = $uniquePrices->count() === 1
                                        ? 'EUR ' . number_format($uniquePrices->first(), 2, ',', '.')
                                        : 'Variabile';
                                @endphp
                                <tr>
                                    <td><strong>{{ strtoupper($groupColorCode) }}</strong></td>
                                    <td class="text-right meters">{{ number_format((float) $colorTotalMeters, 2, ',', '.') }} mt</td>
                                    <td class="text-right {{ $uniquePrices->count() === 1 ? '' : 'muted' }}">{{ $priceLabel }}</td>
                                    <td class="text-right price">EUR {{ number_format((float) $colorTotalCost, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td><strong>TOTALE {{ strtoupper($fabricName) }}:</strong></td>
                                <td class="text-right"><strong>{{ number_format((float) $fabricTotalMeters, 2, ',', '.') }} mt</strong></td>
                                <td></td>
                                <td class="text-right"><strong>EUR {{ number_format((float) $fabricTotalCost, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
