@extends('pdf.layouts.dress-document', ['title' => 'Modellino Aggiuntivo Abito #' . $dress->id])

@section('content')
    <div class="document-page">
        <h1 class="page-title">Modellino aggiuntivo</h1>
        <div class="page-subtitle">Cambio abito / Seconda proposta</div>

        <table class="meta-table" style="margin-bottom: 6mm;">
            <tr>
                <td class="label">Cliente</td>
                <td>{{ $dress->customer_name ?: '-' }}</td>
                <td class="label">Telefono</td>
                <td>{{ $dress->phone_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Consegna</td>
                <td>{{ $dress->delivery_date?->format('d/m/Y') ?: '-' }}</td>
                <td class="label">Abito</td>
                <td>#{{ $dress->id }}</td>
            </tr>
        </table>

        <div class="image-frame" style="height: 205mm; margin-bottom: 6mm;">
            @if(! empty($document['secondary_model_image_path']))
                <img src="{{ $document['secondary_model_image_path'] }}" alt="Modellino aggiuntivo" style="width: auto; max-width: 100%; height: auto; max-height: 205mm;">
            @else
                <div class="image-placeholder">Nessun modellino aggiuntivo caricato.</div>
            @endif
        </div>

        @if(filled($document['secondary_model_notes'] ?? null))
            <div class="section-title">Note</div>
            <div class="small-text" style="white-space: pre-line;">{{ $document['secondary_model_notes'] }}</div>
        @endif
    </div>
@endsection
