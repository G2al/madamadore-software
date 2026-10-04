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
    </div>

    @php
        $fabricsBySupplier = $fabrics->groupBy(fn ($fabric) => $fabric->supplier ?: 'Senza fornitore');
    @endphp

    @foreach($fabricsBySupplier as $supplier => $supplierItems)
        @php
            $supplierTotalMeters = $supplierItems->sum('meters');
            $fabricsByName = $supplierItems->groupBy(fn ($item) => $item->name ?: 'Tessuto senza nome');
        @endphp

        <div class="supplier-section">
            <div class="supplier-header">
                FORNITORE: {{ strtoupper($supplier) }}
                <span style="float: right;">
                    Totale: {{ number_format((float) $supplierTotalMeters, 2, ',', '.') }} mt
                </span>
            </div>

            @foreach($fabricsByName as $fabricName => $fabricItems)
                @php
                    $fabricTotalMeters = $fabricItems->sum('meters');
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
                            Totale: {{ number_format((float) $fabricTotalMeters, 2, ',', '.') }} mt
                        </span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60%;">CODICE COLORE</th>
                                <th style="width: 40%;">METRI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fabricColors as $groupColorCode => $colorItems)
                                @php
                                    $colorTotalMeters = $colorItems->sum('meters');
                                @endphp
                                <tr>
                                    <td><strong>{{ strtoupper($groupColorCode) }}</strong></td>
                                    <td class="text-right meters">{{ number_format((float) $colorTotalMeters, 2, ',', '.') }} mt</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td><strong>TOTALE {{ strtoupper($fabricName) }}:</strong></td>
                                <td class="text-right"><strong>{{ number_format((float) $fabricTotalMeters, 2, ',', '.') }} mt</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
