<?php

namespace App\Services;

use App\Models\Dress;
use App\Models\DressMeasurement;

class MeasurementRecallService
{
    /**
     * Ritorna una lista deduplicata di clienti presenti nei Dress.
     * Chiave = "customer_name|phone_number".
     * Valore = etichetta leggibile (es. "Luigi Iommelli — 329...").
     */
    public static function distinctCustomers(): array
    {
        $rows = Dress::query()
            ->select(['customer_name', 'phone_number'])
            ->whereNotNull('customer_name')
            ->orderBy('customer_name')
            ->get()
            ->map(fn ($r) => [
                'name'  => trim((string) $r->customer_name),
                'phone' => trim((string) ($r->phone_number ?? '')),
            ])
            ->unique(fn ($r) => $r['name'] . '|' . $r['phone'])
            ->values();

        $out = [];
        foreach ($rows as $r) {
            $key   = $r['name'] . '|' . $r['phone'];
            $label = $r['name'] . ($r['phone'] !== '' ? ' — ' . $r['phone'] : '');
            $out[$key] = $label;
        }

        return $out;
    }

    /**
     * Ritorna i clienti deduplicati con info sull’ultimo abito (cerimonia e data).
     */
    public static function distinctCustomersWithLastEvent(): array
    {
        $ceremonyMap = config('dress.ceremonies', []);

        $rows = Dress::withoutGlobalScope('notArchived')
            ->select([
                'customer_name',
                'phone_number',
                'ceremony_type',
                'ceremony_date',
                'created_at'
            ])
            ->whereNotNull('customer_name')
            ->orderByDesc('created_at')
            ->get();

        $unique = $rows->unique(fn ($r) =>
            trim((string) $r->customer_name) . '|' . trim((string) ($r->phone_number ?? ''))
        )->values();

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

        ksort($out, SORT_LOCALE_STRING);
        return $out;
    }

    /**
     * Trova l’ultimo Dress del cliente selezionato.
     */
    public static function findLastDress(string $customerName, ?string $phone = null, ?int $excludeDressId = null): ?Dress
    {
        $q = Dress::withoutGlobalScope('notArchived')
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
     * Esporta le misure dal Dress sorgente.
     */
    public static function exportFromDress(Dress $source): array
    {
        $measurementsItem = [];

        if ($source->measurements) {
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

        $measurements = empty($measurementsItem) ? [] : [$measurementsItem];

        return [
            'measurements'       => $measurements,
            'customMeasurements' => $customItems,
        ];
    }

    /**
     * Applica export allo stato attuale del form.
     */
    public static function applyToState(
        array $currentMeasurements,
        array $currentCustoms,
        array $export,
        string $mode = 'replace',
        bool $includeCustom = true,
        bool $mergeCustomByLabel = true,
    ): array {
        /** -----------------  MISURE FISSE ----------------- */

        $exportItem = $export['measurements'][0] ?? null;

        if ($exportItem === null) {
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

        /** -----------------  MISURE PERSONALIZZATE ----------------- */

        $newCustoms = $currentCustoms;

        if ($includeCustom) {
            $exportCustoms = $export['customMeasurements'] ?? [];

            if ($mode === 'replace') {
                $newCustoms = $exportCustoms;
            } else { // fill
                if ($mergeCustomByLabel) {
                    $existingLabels = collect($currentCustoms)
                        ->pluck('label')
                        ->filter()
                        ->map(fn ($s) => mb_strtolower(trim($s)))
                        ->values();

                    foreach ($exportCustoms as $row) {
                        $label = mb_strtolower(trim((string) ($row['label'] ?? '')));
                        if ($label === '' || $existingLabels->contains($label)) {
                            continue;
                        }
                        $newCustoms[] = $row;
                    }
                } else {
                    $newCustoms = array_merge($currentCustoms, $exportCustoms);
                }
            }
        }

        if (count($newMeasurements) > 1) {
            $newMeasurements = [$newMeasurements[0]];
        }

        return [
            'measurements'       => $newMeasurements,
            'customMeasurements' => array_values($newCustoms),
        ];
    }

    /**
     * Wrapper finale: importa misure + nome + telefono.
     *
     * @return array{
     *     measurements: array,
     *     customMeasurements: array,
     *     sourceDressId: int|null,
     *     sourceCustomerName: string|null,
     *     sourcePhoneNumber: string|null
     * }
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
        $name  = trim((string) $name);
        $phone = trim((string) $phone);

        $source = self::findLastDress($name, $phone, $excludeDressId);

        if (!$source) {
            return [
                'measurements'       => $currentMeasurements,
                'customMeasurements' => $currentCustoms,
                'sourceDressId'      => null,
                'sourceCustomerName' => null,
                'sourcePhoneNumber'  => null,
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

        return $applied + [
            'sourceDressId'      => $source->id,
            'sourceCustomerName' => $source->customer_name,
            'sourcePhoneNumber'  => $source->phone_number,
        ];
    }
}
