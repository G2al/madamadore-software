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
        .color-group { 
            margin-bottom: 20px; 
            border: 1px solid #ddd;
        }
        .color-header { 
            background: #ecf0f1; 
            padding: 8px 12px; 
            font-weight: bold; 
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
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
        .text-center { text-align: center; }
        .total-row { 
            background: #e8f5e8; 
            font-weight: bold; 
            border-top: 2px solid #27ae60;
        }
        .grand-total {
            background: #d5e8d4;
            font-size: 13px;
            font-weight: bold;
        }
        .price { color: #e74c3c; font-weight: bold; }
        .meters { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTA ACQUISTI TESSUTI</h1>
        <p><strong>Generata il:</strong> {{ $generatedAt }}</p>
        <p class="price"><strong>TOTALE COMPLESSIVO: € {{ number_format($totalCost, 2, ',', '.') }}</strong></p>
    </div>

    @php 
        $fabricsBySupplierAndColor = $fabrics->groupBy(['supplier', 'color_code']);
    @endphp

    @foreach($fabricsBySupplierAndColor as $supplier => $colorGroups)
        <div class="supplier-section">
            <div class="supplier-header">
                FORNITORE: {{ strtoupper($supplier) }}
            </div>
            
            @foreach($colorGroups as $colorCode => $items)
                @php
                    $colorTotalMeters = $items->sum('meters');
                    $colorTotalCost = $items->sum(function($item) { 
                        return $item->meters * $item->purchase_price; 
                    });
                @endphp
                
                <div class="color-group">
                    <div class="color-header">
                      CODICE COLORE: {{ $colorCode }} 
                        <span style="float: right;">
                            Totale: {{ number_format($colorTotalMeters, 2, ',', '.') }} mt - € {{ number_format($colorTotalCost, 2, ',', '.') }}
                        </span>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 20%;">TESSUTO</th>
                                <th style="width: 20%;">CLIENTE/ABITO</th>
                                <th style="width: 12%;">CONSEGNA</th>
                                <th style="width: 12%;">METRI</th>
                                <th style="width: 12%;">PREZZO/MT</th>
                                <th style="width: 12%;">SUBTOTALE</th>
                                <th style="width: 12%;">URGENZA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items->sortBy('dress.delivery_date') as $fabric)
                                @php 
                                    $subtotal = $fabric->meters * $fabric->purchase_price;
                                    $deliveryDate = $fabric->dress->delivery_date;
                                    $urgency = '';
                                    $urgencyColor = '';
                                    
                                    if ($deliveryDate) {
                                        $daysUntil = \Carbon\Carbon::parse($deliveryDate)->diffInDays(now(), false);
                                        if ($daysUntil > 0) {
                                            $urgency = 'SCADUTO';
                                            $urgencyColor = 'color: #e74c3c; font-weight: bold;';
                                        } elseif ($daysUntil >= -7) {
                                            $urgency = 'URGENTE';
                                            $urgencyColor = 'color: #f39c12; font-weight: bold;';
                                        } elseif ($daysUntil >= -14) {
                                            $urgency = 'PROSSIMO';
                                            $urgencyColor = 'color: #3498db;';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><strong>{{ $fabric->name }}</strong></td>
                                    <td>{{ $fabric->dress->customer_name }}</td>
                                    <td class="text-center">{{ $deliveryDate ? $deliveryDate->format('d/m/Y') : 'Non definita' }}</td>
                                    <td class="text-right meters">{{ number_format($fabric->meters, 2, ',', '.') }} mt</td>
                                    <td class="text-right">€ {{ number_format($fabric->purchase_price, 2, ',', '.') }}</td>
                                    <td class="text-right price">€ {{ number_format($subtotal, 2, ',', '.') }}</td>
                                    <td class="text-center" style="{{ $urgencyColor }}">{{ $urgency }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="3"><strong>TOTALE {{ $colorCode }}:</strong></td>
                                <td class="text-right"><strong>{{ number_format($colorTotalMeters, 2, ',', '.') }} mt</strong></td>
                                <td></td>
                                <td class="text-right"><strong>€ {{ number_format($colorTotalCost, 2, ',', '.') }}</strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>