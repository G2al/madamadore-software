<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ ucfirst($periodType) }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111111;
            margin: 18px;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin: 0 0 4px;
        }

        h2 {
            font-size: 12px;
            text-align: center;
            margin: 0 0 18px;
            color: #555555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #cccccc;
            padding: 5px 4px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .small {
            font-size: 8px;
            color: #555555;
        }

        .status-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            background-color: #e5e7eb;
        }

        .items {
            margin: 0;
            padding-left: 12px;
        }

        .items li {
            margin-bottom: 2px;
        }

        .footer {
            margin-top: 18px;
            font-size: 8px;
            text-align: right;
            color: #666666;
        }
    </style>
</head>
<body>
    @php
        $periodLabel = $periodType === 'giorno'
            ? $startDate->translatedFormat('d F Y')
            : $startDate->translatedFormat('d F Y') . ' - ' . $endDate->translatedFormat('d F Y');
    @endphp

    <h1>{{ $title }} da consegnare</h1>
    <h2>{{ ucfirst($periodType) }}: {{ $periodLabel }}</h2>

    @if($records->isEmpty())
        <p class="text-center" style="margin-top: 30px;">
            Nessun aggiusto con data di consegna nel periodo selezionato.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 19%;">Cliente</th>
                    <th style="width: 12%;">Telefono</th>
                    <th style="width: 10%;">Consegna</th>
                    <th style="width: 12%;">Stato</th>
                    <th style="width: 29%;">Aggiusti</th>
                    <th style="width: 10%;">Lavorante</th>
                    <th style="width: 8%;" class="text-right">Totale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    @php
                        $statusLabel = $statusLabels[$record->status] ?? ucfirst(str_replace('_', ' ', (string) $record->status));
                        $workers = $record->items
                            ->pluck('worker.name')
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $record->customer?->name ?? '-' }}</strong>
                            @if($record->referente)
                                <br><span class="small">Ref. {{ $record->referente }}</span>
                            @endif
                        </td>
                        <td>{{ $record->customer?->phone_number ?? '-' }}</td>
                        <td class="text-center">
                            {{ $record->delivery_date ? $record->delivery_date->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <span class="status-pill">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            @if($record->items->isEmpty())
                                -
                            @else
                                <ul class="items">
                                    @foreach($record->items as $item)
                                        <li>
                                            <strong>{{ $item->name ?? '-' }}</strong>
                                            @if($item->description)
                                                <br><span class="small">{{ $item->description }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td>
                            @if($workers->isNotEmpty())
                                {{ $workers->join(', ') }}
                            @else
                                {{ $record->primaryWorker?->name ?? '-' }}
                            @endif
                        </td>
                        <td class="text-right">
                            {{ number_format((float) $record->client_price, 2, ',', '.') }} EUR
                            @if((float) $record->remaining > 0)
                                <br><span class="small">Residuo {{ number_format((float) $record->remaining, 2, ',', '.') }} EUR</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generato il {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
