<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Scheda Abito #{{ $dress->id }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #333; 
            margin: 0;
            padding: 0;
        }

        /* HEADER COMPATTO (senza logo e indirizzo) */
        .header { 
            text-align: center; 
            margin-bottom: 10px; 
            border-bottom: 1px solid #000000ff;
            padding-bottom: 5px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 16px; 
            color: #000000ff; 
        }

        .customer-info {
            margin-bottom: 10px;
            padding: 6px;
            background-color: #f5f5f5;
            border-radius: 3px;
            font-size: 10px;
        }

        .description-section {
            margin-bottom: 10px;
            padding: 6px;
            border: 1px solid #000000ff;
            border-radius: 3px;
        }
        .description-title {
            color: #000000ff;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
            border-bottom: 1px solid #000000ff;
            padding-bottom: 2px;
        }
        .description-content {
            font-size: 10px;
            line-height: 1.3;
            color: #555;
        }

        .main-content {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .left-column {
            display: table-cell;
            width: 75%; /* PIÙ GRANDE per la foto */
            vertical-align: top;
            padding-right: 10px;
        }
        .right-column {
            display: table-cell;
            width: 25%; /* PIÙ STRETTO per le misure */
            vertical-align: top;
        }

        .image-container {
            text-align: center;
            border: 2px solid #000000ff;
            border-radius: 5px;
            padding: 5px;
            background-color: #f9f9f9;
        }
        .image-container img {
            max-width: 100%;
            max-height: 1000px; /* GIGANTE */
            border-radius: 3px;
        }
        .no-image {
            padding: 250px 20px;
            color: #000000ff;
            border: 2px dashed #000000ff;
            border-radius: 5px;
            text-align: center;
        }

        .measurements-title {
            color: #000000ff;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
            border-bottom: 1px solid #000000ff;
            padding-bottom: 2px;
        }
        .measurements-list {
            font-size: 9px;
            line-height: 1.3;
        }
        .measurement-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .measurement-label {
            display: table-cell;
            font-weight: bold;
            color: #000000ff;
            width: 65%; /* STRINGE label */
            padding-right: 3px;
        }
        .measurement-value {
            display: table-cell;
            text-align: right;
            width: 35%; /* PIÙ VICINO al testo */
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>Scheda Abito #{{ $dress->id }}</h1>
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
        <!-- Colonna Sinistra - Foto (GIGANTE) -->
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

        <!-- Colonna Destra - Misure (Compatta) -->
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

            <!-- Misure Personalizzate -->
            @if($dress->customMeasurements && $dress->customMeasurements->count() > 0)
                <div class="measurements-title" style="margin-top: 10px;">MISURE PERSONALIZZATE</div>
                <div class="measurements-list">
                    @foreach($dress->customMeasurements as $customMeasurement)
                        <div class="measurement-row">
                            <div class="measurement-label">{{ $customMeasurement->label }}</div>
                            <div class="measurement-value">
                                @if($customMeasurement->value)
                                    {{ $customMeasurement->value }} {{ $customMeasurement->unit }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        @if($customMeasurement->notes)
                            <div style="font-size: 8px; color: #666; margin-left: 5px; margin-bottom: 3px;">
                                <em>Note: {{ $customMeasurement->notes }}</em>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</body>
</html>
