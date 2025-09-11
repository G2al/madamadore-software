<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ricevuta Aggiusto #{{ $adjustment->id }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 14px; 
            color: #333; 
            background-color: #EAF2EF;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            background-color: #914D76;
            color: white;
            padding: 20px;
            border-radius: 8px;
        }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0 0; font-size: 16px; opacity: 0.9; }
        
        .info, .details, .items { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        .info td, .details td, .details th, .items td, .items th {
            border: 1px solid #ddd; 
            padding: 10px;
        }
        .details th, .items th { 
            background-color: #914D76; 
            color: white; 
            text-align: left; 
            font-weight: bold;
        }
        .info td {
            background-color: #f9f9f9;
        }
        .items td {
            vertical-align: top;
        }
        .item-name {
            font-weight: bold;
            color: #914D76;
        }
        .item-description {
            font-style: italic;
            color: #666;
            margin-top: 5px;
            line-height: 1.4;
        }
        .totals-section {
            background-color: #EAF2EF;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 12px; 
            color: #777; 
            border-top: 2px solid #914D76;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Madamadorè Software</h1>
            <p>Ricevuta Aggiusto</p>
        </div>

        <table class="info">
            <tr>
                <td><strong>ID Aggiusto:</strong> #{{ $adjustment->id }}</td>
                <td><strong>Data:</strong> {{ $adjustment->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong> {{ $adjustment->customer->name ?? '-' }}</td>
                <td><strong>Telefono:</strong> {{ $adjustment->customer->phone_number ?? '-' }}</td>
            </tr>
        </table>

        @if($adjustment->items && $adjustment->items->count() > 0)
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 30%;">Aggiusto</th>
                    <th style="width: 70%;">Descrizione Lavoro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustment->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                    </td>
                    <td>
                        <div class="item-description">
                            {{ $item->description ?: 'Nessuna descrizione fornita' }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="totals-section">
            <table class="details">
                <thead>
                    <tr>
                        <th>Prezzo Cliente</th>
                        <th>Acconto</th>
                        <th>Totale</th>
                        <th>Rimanente</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>€ {{ number_format($adjustment->client_price, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($adjustment->deposit, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($adjustment->total, 2, ',', '.') }}</td>
                        <td>€ {{ number_format($adjustment->remaining, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Questa ricevuta è stata generata automaticamente da Madamadorè Software.</p>
        </div>
    </div>
</body>
</html>