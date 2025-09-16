<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Scheda Abito #{{ $dress->id }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #333; 
            margin: 0;
            padding: 0;
        }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #914D76;
            padding-bottom: 10px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 18px; 
            color: #914D76; 
        }
        .customer-info {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 3px;
            font-size: 10px;
        }
        .description-section {
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #914D76;
            border-radius: 3px;
        }
        .description-title {
            color: #914D76;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            border-bottom: 1px solid #914D76;
            padding-bottom: 3px;
        }
        .description-content {
            font-size: 10px;
            line-height: 1.4;
            color: #555;
        }
        .main-content {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .left-column {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            padding-right: 15px;
        }
        .right-column {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }
        .image-container {
            text-align: center;
            border: 2px solid #914D76;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .image-container img {
            max-width: 100%;
            max-height: 350px;
            border-radius: 3px;
        }
        .no-image {
            padding: 80px 20px;
            color: #914D76;
            border: 2px dashed #914D76;
            border-radius: 5px;
            text-align: center;
        }
        .measurements-title {
            color: #914D76;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 12px;
            border-bottom: 1px solid #914D76;
            padding-bottom: 3px;
        }
        .measurements-list {
            font-size: 10px;
            line-height: 1.4;
        }
        .measurement-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }
        .measurement-label {
            display: table-cell;
            font-weight: bold;
            color: #914D76;
            width: 70%;
            padding-right: 5px;
        }
        .measurement-value {
            display: table-cell;
            text-align: right;
            width: 30%;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Madamadorè - Scheda Abito #{{ $dress->id }}</h1>
    </div>

    <div class="customer-info">
        <strong>Cliente:</strong> {{ $dress->customer_name }} | 
        <strong>Tel:</strong> {{ $dress->phone_number }} | 
        <strong>Cerimonia:</strong> {{ $dress->ceremony_type }} ({{ $dress->ceremony_date?->format('d/m/Y') }})
    </div>

    <!-- Sezione Descrizione/Note -->
    @if($dress->notes)
    <div class="description-section">
        <div class="description-title">DESCRIZIONE ABITO</div>
        <div class="description-content">{{ $dress->notes }}</div>
    </div>
    @endif

    <div class="main-content">
        <!-- Colonna Sinistra - Foto -->
        <div class="left-column">
            <div class="image-container">
                @if($dress->final_image)
                    <img src="{{ storage_path('app/public/' . $dress->final_image) }}" alt="Abito Definitivo">
                @else
                    <div class="no-image">
                        Foto Definitivo<br>Non Disponibile
                    </div>
                @endif
            </div>
        </div>

        <!-- Colonna Destra - Misure -->
        <div class="right-column">
            <div class="measurements-title">MISURE CLIENTE</div>
            
            @if($dress->measurements)
                <div class="measurements-list">
                    @foreach(\App\Models\DressMeasurement::ORDERED_MEASURES as $field => $label)
                        @if($dress->measurements->$field)
                            <div class="measurement-row">
                                <div class="measurement-label">{{ $label }}</div>
                                <div class="measurement-value">
                                    {{ $dress->measurements->$field }} {{ $field === 'inclinazione_spalle' ? '°' : 'cm' }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div style="color: #999; font-style: italic;">Nessuna misura disponibile</div>
            @endif
        </div>
    </div>
</body>
</html>