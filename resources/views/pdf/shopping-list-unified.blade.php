<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 18px;
            color: #333;
        }

        .page-break {
            page-break-before: always;
        }

        .header {
            text-align: center;
            margin-bottom: 22px;
            border-bottom: 2px solid #2f3e4d;
            padding-bottom: 12px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 20px;
            color: #243444;
            text-transform: uppercase;
        }

        .header p {
            margin: 3px 0;
        }

        .supplier-section {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .supplier-header {
            background: #304456;
            color: #fff;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: bold;
        }

        .supplier-summary {
            float: right;
            font-size: 10px;
            font-weight: normal;
        }

        .group-card {
            border: 1px solid #d8dde3;
            margin-top: 14px;
        }

        .group-header {
            background: #edf2f7;
            padding: 8px 12px;
            border-bottom: 1px solid #d8dde3;
            font-weight: bold;
            color: #243444;
        }

        .group-header .subtitle {
            font-size: 10px;
            font-weight: normal;
            color: #627080;
        }

        .group-header .totals {
            float: right;
            font-size: 10px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d8dde3;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #7a8694;
        }

        .supplier-footer {
            margin-top: 10px;
            font-size: 10px;
            color: #65717d;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
        <p><strong>Generata il:</strong> {{ $generatedAt }}</p>
        <p><strong>Voci totali:</strong> {{ $overallRows }} | <strong>Totale complessivo:</strong> EUR {{ number_format($overallTotalCost, 2, ',', '.') }}</p>
    </div>

    @forelse($supplierGroups as $supplierGroup)
        <div class="supplier-section @if(! $loop->first) page-break @endif">
            <div class="supplier-header">
                FORNITORE: {{ strtoupper($supplierGroup['name']) }}
                <span class="supplier-summary">
                    @if(! empty($supplierGroup['unit_totals']))
                        @foreach($supplierGroup['unit_totals'] as $unitTotal)
                            {{ number_format($unitTotal['quantity'], 2, ',', '.') }} {{ $unitTotal['unit'] }}@if(! $loop->last) · @endif
                        @endforeach
                        ·
                    @endif
                    EUR {{ number_format($supplierGroup['total_cost'], 2, ',', '.') }}
                </span>
            </div>

            @foreach($supplierGroup['groups'] as $group)
                <div class="group-card">
                    <div class="group-header">
                        {{ strtoupper($group['name']) }}
                        @if($group['subtitle'] !== '')
                            <span class="subtitle">({{ $group['subtitle'] }})</span>
                        @endif
                        <span class="totals">
                            @if(! empty($group['unit_totals']))
                                @foreach($group['unit_totals'] as $unitTotal)
                                    {{ number_format($unitTotal['quantity'], 2, ',', '.') }} {{ $unitTotal['unit'] }}@if(! $loop->last) · @endif
                                @endforeach
                                ·
                            @endif
                            EUR {{ number_format($group['total_cost'], 2, ',', '.') }}
                        </span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 38%;">Colore / Variante</th>
                                <th style="width: 18%;">Quantità</th>
                                <th style="width: 18%;">Prezzo unitario</th>
                                <th style="width: 26%;">Subtotale</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['rows'] as $row)
                                <tr>
                                    <td><strong>{{ $row['variant'] }}</strong></td>
                                    <td class="text-right">
                                        @if($row['quantity'] !== null)
                                            {{ number_format($row['quantity'], 2, ',', '.') }} {{ $row['unit'] }}
                                        @else
                                            <span class="muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($row['price'] !== null)
                                            EUR {{ number_format($row['price'], 2, ',', '.') }}
                                        @else
                                            <span class="muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right">EUR {{ number_format($row['subtotal'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="supplier-footer">
                Foglio fornitore generato automaticamente dal gestionale
            </div>
        </div>
    @empty
        <p class="muted">Nessuna voce disponibile per la stampa.</p>
    @endforelse
</body>
</html>
