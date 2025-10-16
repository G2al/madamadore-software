<?php

namespace App\Services;

use App\Models\Dress;
use App\Models\DressMeasurement;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MeasurementRecallService
{
    /**
     * Ritorna una lista deduplicata di clienti presenti nei Dress.
     * Chiave = "customer_name|phone_number" (phone opzionale).
     * Valore = etichetta leggibile (es. "Luigi Iommelli — 329...").
     */
    public static function distinctCustomers(): array
    {
        // Preleva solo i campi necessari e deduplica su name+phone
        $rows = Dress::query()
            ->select(['customer_name', 'phone_number'])
            ->whereNotNull('customer_name')
            ->orderBy('customer_name')
            ->get()
            ->map(fn ($r) => [
                'name' => trim((string) $r->customer_name),
                'phone' => trim((string) ($r->phone_number ?? '')),
            ])
            ->unique(fn ($r) => $r['name'] . '|' . $r['phone'])
            ->values();

        $out = [];
        foreach ($rows as $r) {
            $key = $r['name'] . '|' . $r['phone'];
            $label = $r['name'] . ($r['phone'] !== '' ? ' — ' . $r['phone'] : '');
            $out[$key] = $label;
        }

        return $out;
    }

    public static function distinctCustomersWithLastEvent(): array
{
    $ceremonyMap = config('dress.ceremonies', []);

    $rows = \App\Models\Dress::withoutGlobalScope('notArchived') // 👈 include anche gli archiviati

        ->select(['customer_name', 'phone_number', 'ceremony_type', 'ceremony_date', 'created_at'])
        ->whereNotNull('customer_name')
        ->orderByDesc('created_at') // così unique() tiene l'ULTIMO abito
        ->get();

    $unique = $rows->unique(fn ($r) => trim((string) $r->customer_name) . '|' . trim((string) ($r->phone_number ?? '')))
        ->values();

    $out = [];
    foreach ($unique as $r) {
        $name  = trim((string) $r->customer_name);
        $phone = trim((string) ($r->phone_number ?? ''));

        $cerType = $r->ceremony_type
            ? ($ceremonyMap[$r->ceremony_type] ?? ucfirst($r->ceremony_type))
            : '—';

        $cerDate = $r->ceremony_date
            ? $r->ceremony_date->format('d/m/Y')
            : '—';

        $key   = $name . '|' . $phone;
        $label = $name
            . ($phone !== '' ? ' — ' . $phone : '')
            . '  |  Ultimo: ' . $cerType
            . ($cerDate !== '—' ? ' (' . $cerDate . ')' : '');

        $out[$key] = $label;
    }

    ksort($out, SORT_LOCALE_STRING); // opzionale: ordina alfabetico per etichetta
    return $out;
}


    /**
     * Trova l'ultimo Dress per un determinato cliente (per nome, opzionale telefono).
     * Esclude un record specifico se stai editando (per evitare di copiare da sé stesso).
     */
    public static function findLastDress(string $customerName, ?string $phone = null, ?int $excludeDressId = null): ?Dress
    {
        
    $q = Dress::withoutGlobalScope('notArchived') // 👈 include anche gli archiviati
        ->where('customer_name', $customerName);


        if (!empty($phone)) {
            $q->where('phone_number', $phone);
        }

        if ($excludeDressId) {
            $q->where('id', '!=', $excludeDressId);
        }

        return $q->with(['measurements', 'customMeasurements'])
            ->latest('created_at')
            ->first();
    }

    /**
     * Esporta lo state "grezzo" delle misure (fisse + personalizzate) da un Dress sorgente,
     * nel formato atteso dal form di Filament (Repeater measurements + Repeater customMeasurements).
     */
    public static function exportFromDress(Dress $source): array
    {
        $measurementsItem = [];

        if ($source->measurements) {
            // Prendi i campi dal fillable del Model per restare allineati allo schema
            $fields = array_values(array_filter(
                (new DressMeasurement())->getFillable(),
                fn ($f) => !in_array($f, ['id', 'dress_id'], true)
            ));

            foreach ($fields as $field) {
                $measurementsItem[$field] = $source->measurements->{$field};
            }
        }

        $customItems = $source->customMeasurements
            ->map(fn ($cm) => [
                'label' => $cm->label,
                'value' => $cm->value,
                'notes' => $cm->notes,
            ])
            ->values()
            ->all();

        // Il repeater measurements nel tuo form accetta al massimo 1 item.
        $measurements = empty($measurementsItem) ? [] : [ $measurementsItem ];

        return [
            'measurements'        => $measurements,
            'customMeasurements'  => $customItems,
        ];
    }

    /**
     * Applica l'export allo state corrente del form.
     *
     * @param array $currentMeasurements  Stato attuale del repeater "measurements" (al massimo 1 item).
     * @param array $currentCustoms       Stato attuale del repeater "customMeasurements".
     * @param array $export               Output di exportFromDress().
     * @param 'replace'|'fill' $mode      replace = sostituisci tutto, fill = solo campi vuoti.
     * @param bool $includeCustom         Se includere anche le misure personalizzate.
     * @param bool $mergeCustomByLabel    Se true in modalità fill, non duplica per stessa label.
     *
     * @return array{measurements: array, customMeasurements: array}
     */
    public static function applyToState(
        array $currentMeasurements,
        array $currentCustoms,
        array $export,
        string $mode = 'replace',
        bool $includeCustom = true,
        bool $mergeCustomByLabel = true,
    ): array {
        // --- Measurements (fisse) ---
        $exportItem = $export['measurements'][0] ?? null;

        if ($exportItem === null) {
            // Nessuna misura disponibile: non tocchiamo lo state esistente
            $newMeasurements = $currentMeasurements;
        } else {
            $currentItem = $currentMeasurements[0] ?? [];

            if ($mode === 'replace') {
                $newMeasurements = [$exportItem];
            } else { // fill
                $merged = $currentItem;
                foreach ($exportItem as $k => $v) {
                    $isEmpty = !isset($merged[$k]) || $merged[$k] === '' || $merged[$k] === null;
                    if ($isEmpty && $v !== null && $v !== '') {
                        $merged[$k] = $v;
                    }
                }
                $newMeasurements = [$merged];
            }
        }

        // --- Custom measurements ---
        $newCustoms = $currentCustoms;

        if ($includeCustom) {
            $exportCustoms = $export['customMeasurements'] ?? [];

            if ($mode === 'replace') {
                $newCustoms = $exportCustoms;
            } else { // fill
                if ($mergeCustomByLabel) {
                    // Aggiunge solo quelle che non esistono già per label
                    $existingLabels = collect($currentCustoms)->pluck('label')->filter()->map(fn ($s) => mb_strtolower(trim($s)))->values();
                    foreach ($exportCustoms as $row) {
                        $label = mb_strtolower(trim((string) ($row['label'] ?? '')));
                        if ($label === '' || $existingLabels->contains($label)) {
                            continue;
                        }
                        $newCustoms[] = $row;
                    }
                } else {
                    // Appende tutte in coda
                    $newCustoms = array_merge($currentCustoms, $exportCustoms);
                }
            }
        }

        // Normalizza: measurements max 1 item
        if (count($newMeasurements) > 1) {
            $newMeasurements = [ $newMeasurements[0] ];
        }

        return [
            'measurements' => $newMeasurements,
            'customMeasurements' => array_values($newCustoms),
        ];
    }

    /**
     * Comodo wrapper: dal "customerKey" (name|phone) trova l'ultimo Dress,
     * esporta e applica all'attuale state secondo le opzioni.
     *
     * @return array{measurements: array, customMeasurements: array, sourceDressId: int|null}
     */
    public static function recallForCustomerKey(
        string $customerKey,
        ?int $excludeDressId,
        array $currentMeasurements,
        array $currentCustoms,
        string $mode = 'replace',
        bool $includeCustom = true,
        bool $mergeCustomByLabel = true,
    ): array {
        [$name, $phone] = array_pad(explode('|', $customerKey, 2), 2, null);
        $name = trim((string) $name);
        $phone = trim((string) $phone);

        $source = self::findLastDress($name, $phone, $excludeDressId);

        if (!$source) {
            // Nessuna sorgente: ritorna lo state invariato
            return [
                'measurements' => $currentMeasurements,
                'customMeasurements' => $currentCustoms,
                'sourceDressId' => null,
            ];
        }

        $export = self::exportFromDress($source);

        $applied = self::applyToState(
            $currentMeasurements,
            $currentCustoms,
            $export,
            $mode,
            $includeCustom,
            $mergeCustomByLabel
        );

        return $applied + ['sourceDressId' => $source->id];
    }
}
