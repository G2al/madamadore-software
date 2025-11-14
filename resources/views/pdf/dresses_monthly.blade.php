<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Abiti da consegnare – {{ ucfirst($monthLabel) }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111111;
            margin: 20px;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 12px;
            text-align: center;
            margin-bottom: 20px;
            color: #555555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #cccccc;
            padding: 6px 4px;
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

        .small {
            font-size: 9px;
        }

        .status-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
        }

        .status-default {
            background-color: #e5e7eb;
            color: #111827;
        }

        .status-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-warning {
            background-color: #fef9c3;
            color: #92400e;
        }

        .status-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-info {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: right;
            color: #666666;
        }
    </style>
</head>
<body>
    @php
        // Mappa colori base in base al config('dress.statuses') se presente
        $statusColors = [
            'success' => 'status-success',
            'warning' => 'status-warning',
            'danger'  => 'status-danger',
            'info'    => 'status-info',
            'gray'    => 'status-default',
            'primary' => 'status-info',
        ];
    @endphp

    <h1>Abiti da consegnare</h1>
    <h2>{{ ucfirst($monthLabel) }}</h2>

    @if($dresses->isEmpty())
        <p class="text-center" style="margin-top: 30px;">
            Nessun abito con data di consegna in questo mese.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 28%;">Cliente</th>
                    <th style="width: 16%;">Telefono</th>
                    <th style="width: 16%;">Data cerimonia</th>
                    <th style="width: 16%;">Data consegna</th>
                    <th style="width: 24%;">Stato</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dresses as $dress)
                    @php
                        $statusConfig = $statuses[$dress->status] ?? null;
                        $statusLabel  = $statusConfig['label'] ?? $dress->status ?? '-';
                        $statusColor  = $statusConfig['color'] ?? 'gray';
                        $pillClass    = $statusColors[$statusColor] ?? 'status-default';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $dress->customer_name ?? '-' }}</strong><br>
                            <span class="small">
                                @if(!empty($dress->ceremony_type))
                                    {{ ucfirst($dress->ceremony_type) }}
                                @else
                                    &nbsp;
                                @endif
                            </span>
                        </td>
                        <td>{{ $dress->phone_number ?? '-' }}</td>
                        <td class="text-center">
                            @if($dress->ceremony_date)
                                {{ \Carbon\Carbon::parse($dress->ceremony_date)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($dress->delivery_date)
                                {{ \Carbon\Carbon::parse($dress->delivery_date)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="status-pill {{ $pillClass }}">
                                {{ $statusLabel }}
                            </span>
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
