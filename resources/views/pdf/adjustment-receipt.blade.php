<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ricevuta Aggiusto #{{ $adjustment->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .info, .details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info td, .details td, .details th {
            border: 1px solid #ddd; padding: 8px;
        }
        .details th { background: #f4f4f4; text-align: left; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
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
        <tr>
            <td colspan="2"><strong>Nome aggiusto:</strong> {{ $adjustment->name }}</td>
        </tr>
    </table>

    <table class="details">
        <thead>
            <tr>
                <th>Descrizione</th>
                <th>Prezzo Cliente</th>
                <th>Acconto</th>
                <th>Totale</th>
                <th>Rimanente</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $adjustment->name }}</td>
                <td>€ {{ number_format($adjustment->client_price, 2, ',', '.') }}</td>
                <td>€ {{ number_format($adjustment->deposit, 2, ',', '.') }}</td>
                <td>€ {{ number_format($adjustment->total, 2, ',', '.') }}</td>
                <td>€ {{ number_format($adjustment->remaining, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Questa ricevuta è stata generata automaticamente da Madamadorè Software.</p>
    </div>
</body>
</html>
