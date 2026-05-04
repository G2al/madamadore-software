@php
    $title = $title ?? 'Misure';
    $measurements = $measurements ?? [];
    $customMeasurements = $customMeasurements ?? [];
@endphp

<table class="measure-table">
    <tr>
        <th colspan="2">{{ $title }}</th>
    </tr>
    @forelse($measurements as $measurement)
        <tr>
            <td>{{ $measurement['label'] }}</td>
            <td style="width: 28%; text-align: center;">
                {{ $measurement['value'] !== '' ? $measurement['value'] . ' ' . $measurement['unit'] : '' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="2" class="muted">Nessuna misura disponibile</td>
        </tr>
    @endforelse

    @if(! empty($customMeasurements))
        <tr>
            <th colspan="2">Misure personalizzate</th>
        </tr>
        @foreach($customMeasurements as $customMeasurement)
            <tr>
                <td>{{ $customMeasurement['label'] }}</td>
                <td style="text-align: center;">{{ $customMeasurement['value'] }}</td>
            </tr>
        @endforeach
    @endif
</table>
