@extends('pdf.layouts.dress-document', ['title' => 'Modellino Abito #' . $dress->id])

@php
    $logoPath = file_exists(public_path('logo_madamadore_dora_maione_pdf.jpg'))
        ? public_path('logo_madamadore_dora_maione_pdf.jpg')
        : (
            file_exists(public_path('logo_madamadore_dora_maione.png'))
                ? public_path('logo_madamadore_dora_maione.png')
                : (
                    file_exists(public_path('logo_madamadore_pdf.jpg'))
                        ? public_path('logo_madamadore_pdf.jpg')
                        : (file_exists(public_path('logo_madamadore.png')) ? public_path('logo_madamadore.png') : null)
                )
        );
@endphp

@section('content')
    <div class="document-page" style="border: 2px solid #ddcabc; padding: 0; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 46mm; left: 10%; width: 80%; text-align: center;">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="MadamaDore" style="width: 96mm; height: auto; display: block; margin: 0 auto;">
            @else
                <div style="font-size: 38px; font-weight: bold;">MadamaDore</div>
            @endif

            <div style="margin-top: 4mm; margin-bottom: 8mm; font-size: 15px; font-style: italic;">
                Scheda cliente
            </div>

            <table style="width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 13px; color: #222;">
                <tr>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; width: 50%; font-style: italic; vertical-align: middle;">Preventivo Nr.</td>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; width: 50%; vertical-align: middle;">{{ $dress->id }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; font-style: italic; vertical-align: middle;">Nome e cognome</td>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; vertical-align: middle;">{{ $dress->customer_name }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; font-style: italic; vertical-align: middle;">Telefono</td>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; vertical-align: middle;">{{ $dress->phone_number }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; font-style: italic; vertical-align: middle;">Data di consegna</td>
                    <td style="border: 1px solid #ddcabc; padding: 6mm 4mm; vertical-align: middle;">{{ $dress->delivery_date?->format('d/m/Y') ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="page-break"></div>
    @include('pdf.partials.dress-model-technical-page', ['dress' => $dress, 'document' => $document])

    <div class="page-break"></div>
    @include('pdf.partials.dress-model-production-page', ['dress' => $dress, 'document' => $document])

    <div class="page-break"></div>
    @include('pdf.partials.dress-model-overview-page', ['dress' => $dress, 'document' => $document])
@endsection
