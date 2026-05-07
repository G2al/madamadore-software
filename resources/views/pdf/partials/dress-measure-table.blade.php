@php
    $title = $title ?? 'Misure';
    $measurements = $measurements ?? [];
    $customMeasurements = $customMeasurements ?? [];
@endphp

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <th colspan="2" style="text-align: left; font-size: 10.5px; padding: 1mm 0 1.4mm 0; border-bottom: 1px solid #d8ccc5; text-transform: uppercase; letter-spacing: 0.05em;">{{ $title }}</th>
    </tr>
    @forelse($measurements as $measurement)
        <tr>
            <td style="padding: 0.45mm 0; border-bottom: 0.4pt solid #ebe4e0; font-size: 10px; line-height: 1.1;">{{ $measurement['label'] }}</td>
            <td style="width: 53%; text-align: right; padding: 0.45mm 0; border-bottom: 0.4pt solid #ebe4e0; font-size: 10px; line-height: 1.1; white-space: nowrap;">
                {{ $measurement['value'] !== '' ? $measurement['value'] . ' ' . $measurement['unit'] : '' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="2" style="font-size: 9px; color: #999; padding: 0.8mm 0;">Nessuna misura disponibile</td>
        </tr>
    @endforelse
    @if(! empty($customMeasurements))
        <tr>
            <th colspan="2" style="font-size: 9px; padding: 1.2mm 0; border-bottom: 0.4pt solid #d8ccc5; text-align: left;">Misure personalizzate</th>
        </tr>
        @foreach($customMeasurements as $cm)
            <tr>
                <td style="padding: 0.45mm 0; border-bottom: 0.4pt solid #ebe4e0; font-size: 9px; line-height: 1.1;">{{ $cm['label'] }}</td>
                <td style="text-align: right; padding: 0.45mm 0; border-bottom: 0.4pt solid #ebe4e0; font-size: 9px; line-height: 1.1;">{{ $cm['value'] }}</td>
            </tr>
        @endforeach
    @endif
</table>
