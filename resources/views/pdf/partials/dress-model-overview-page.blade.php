@php
    $document = $document ?? app(\App\Services\DressPdfDataService::class)->build($dress);
    $frontImage = $document['overview_front_image_path'] ?? $document['design_image_path'] ?? null;
    $backImage = $document['overview_back_image_path'] ?? null;
    $overviewNotes = array_values(array_filter(array_merge(
        $document['description_paragraphs'] ?? [],
        $document['client_notes_paragraphs'] ?? [],
    )));
@endphp

<div class="document-page">
    <div class="page-title">Modellino Abito</div>
    <div class="page-subtitle">Scheda interna con disegno, fronte/retro e misure cliente</div>

    <div class="section-title">Viste abito</div>
    <table class="image-grid">
        <tr>
            <td style="width: {{ $backImage ? '50%' : '100%' }}; padding-right: {{ $backImage ? '2mm' : '0' }};">
                <div class="image-frame image-frame--portrait" style="height: 96mm;">
                    @if($frontImage)
                        <img src="{{ $frontImage }}" alt="Disegno davanti abito">
                    @else
                        <div class="image-placeholder">Vista davanti non disponibile</div>
                    @endif
                </div>
            </td>

            @if($backImage)
                <td style="width: 50%; padding-left: 2mm;">
                    <div class="image-frame image-frame--portrait" style="height: 96mm;">
                        <img src="{{ $backImage }}" alt="Disegno dietro abito">
                    </div>
                </td>
            @endif
        </tr>
    </table>

    <div class="spacer-sm"></div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 40%; vertical-align: top; padding-right: 3mm;">
                @include('pdf.partials.dress-measure-table', [
                    'title' => 'Misure cliente',
                    'measurements' => $document['measurements'],
                    'customMeasurements' => $document['custom_measurements'],
                ])
            </td>
            <td style="width: 60%; vertical-align: top; padding-left: 3mm;">
                <table class="meta-table">
                    <tr>
                        <td class="label">Cliente</td>
                        <td>{{ $dress->customer_name }}</td>
                        <td class="label">Preventivo Nr.</td>
                        <td>{{ $dress->id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Telefono</td>
                        <td>{{ $dress->phone_number }}</td>
                        <td class="label">Consegna</td>
                        <td>{{ $dress->delivery_date?->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Cerimonia</td>
                        <td>{{ $dress->ceremony_type ?: '-' }}</td>
                        <td class="label">Intestatario</td>
                        <td>{{ $dress->ceremony_holder ?: '-' }}</td>
                    </tr>
                </table>

                <div class="spacer-sm"></div>

                <div class="spacer-sm"></div>
                <div class="section-title">Note abito</div>
                <div class="box small-text" style="min-height: 24mm;">
                    <div class="paragraph-list">
                        @forelse($overviewNotes as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @empty
                            <div class="writing-lines">
                                @for($i = 0; $i < 3; $i++)
                                    <div></div>
                                @endfor
                            </div>
                        @endforelse
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
