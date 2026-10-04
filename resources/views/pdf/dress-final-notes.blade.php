@extends('pdf.layouts.dress-document', ['title' => 'Note Finali Abito #' . $dress->id])

@section('content')
    <div class="document-page">
        <h1 class="page-title">Note finali</h1>
        <div class="page-subtitle">Scheda conclusiva abito su misura</div>

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

        @if(! empty($document['final_notes_photo_path']))
            <div class="image-frame" style="height: 145mm; margin-bottom: 6mm;">
                <img src="{{ $document['final_notes_photo_path'] }}" alt="Foto note finali" style="width: auto; max-width: 100%; height: auto; max-height: 145mm;">
            </div>
        @endif

        <div class="section-title">Annotazioni</div>
        <div class="box" style="min-height: {{ empty($document['final_notes_photo_path']) ? '205mm' : '72mm' }}; font-size: 11px; line-height: 1.5; white-space: pre-line;">
            {{ filled($dress->pronta_misura_notes) ? $dress->pronta_misura_notes : 'Nessuna nota finale inserita.' }}
        </div>
    </div>
@endsection
