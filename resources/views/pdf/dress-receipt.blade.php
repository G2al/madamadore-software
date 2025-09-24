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
            border-bottom: 2px solid #000000ff;
            padding-bottom: 10px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 18px; 
            color: #000000ff; 
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
            border: 1px solid #000000ff;
            border-radius: 3px;
        }
        .description-title {
            color: #000000ff;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            border-bottom: 1px solid #000000ff;
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
            width: 70%;
            vertical-align: top;
            padding-right: 15px;
        }
        .right-column {
            display: table-cell;
            width: 30%;
            vertical-align: top;
        }
        .image-container {
            text-align: center;
            border: 2px solid #000000ff;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .image-container img {
            max-width: 100%;
            max-height: 700px;
            border-radius: 3px;
        }
        .no-image {
            padding: 200px 20px;
            color: #000000ff;
            border: 2px dashed #000000ff;
            border-radius: 5px;
            text-align: center;
        }
        .measurements-title {
            color: #000000ff;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 12px;
            border-bottom: 1px solid #000000ff;
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
            color: #000000ff;
            width: 70%;
            padding-right: 5px;
        }
        .measurement-value {
            display: table-cell;
            text-align: right;
            width: 30%;
        }

        .header .logo {
            height: 80px;
            display: block;
            margin: 5px auto;
        }

    </style>
</head>
<body>
    <div class="header">
    <img src="{{ public_path('storage/branding/logo-madamadore.png') }}" alt="MadamaDorè di Dora Maione" class="logo">
    <p>Via delle Acacie 06, 81031 Aversa – CE Tel. 392.244.86.34 – 081.2306277</p>
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

            <!-- Misure Personalizzate -->
            @if($dress->customMeasurements && $dress->customMeasurements->count() > 0)
                <div class="measurements-title" style="margin-top: 15px;">MISURE PERSONALIZZATE</div>
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
                            <div style="font-size: 9px; color: #666; margin-left: 10px; margin-bottom: 5px;">
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