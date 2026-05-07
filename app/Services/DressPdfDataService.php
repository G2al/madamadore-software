<?php

namespace App\Services;

use App\Models\Dress;
use App\Models\DressCorset;
use App\Models\DressMeasurement;
use App\Models\DressTechnicalSheet;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DressPdfDataService
{
    public function build(Dress $dress): array
    {
        $dress->loadMissing([
            'measurements',
            'customMeasurements',
            'fabrics',
            'extras',
            'expenses',
            'corsets',
            'technicalSheet',
        ]);

        $technicalSheet = $dress->technicalSheet;
        $fabrics = $this->buildFabrics($dress);
        $mainFabric = $this->buildPrimaryFabricData($technicalSheet, $fabrics, 0);
        $sleeveFabric = $this->buildPrimaryFabricData($technicalSheet, $fabrics, 1);
        $clientDescription = $this->buildClientDescription($dress, $technicalSheet);
        $technicalDescription = $this->buildTechnicalDescription($dress, $technicalSheet);
        $generalNotes = trim((string) ($dress->notes ?? ''));

        return [
            'design_image_path' => $this->resolvePrimaryDesignImagePath($dress, $technicalSheet),
            'model_cover_image_path' => $this->resolveModelCoverImagePath($dress, $technicalSheet),
            'approved_front_image_path' => $this->resolveApprovedFrontImagePath($dress, $technicalSheet),
            'approved_back_image_path' => $this->resolveApprovedBackImagePath($dress, $technicalSheet),
            'overview_front_image_path' => $this->resolveStoredImagePath($technicalSheet?->front_view_image)
                ?? $this->resolvePrimaryDesignImagePath($dress, $technicalSheet),
            'overview_back_image_path' => $this->resolveStoredImagePath($technicalSheet?->back_view_image),
            'front_view_image_path' => $this->resolveStoredImagePath($technicalSheet?->front_view_image)
                ?? $this->resolvePrimaryDesignImagePath($dress, $technicalSheet),
            'back_view_image_path' => $this->resolveStoredImagePath($technicalSheet?->back_view_image),
            'neckline_detail_image_path' => $this->resolveStoredImagePath($technicalSheet?->neckline_detail_image),
            'sleeve_detail_image_path' => $this->resolveStoredImagePath($technicalSheet?->sleeve_detail_image),
            'bodice_detail_image_path' => $this->resolveStoredImagePath($technicalSheet?->bodice_detail_image),
            'back_detail_image_path' => $this->resolveStoredImagePath($technicalSheet?->back_detail_image),
            'closure_detail_image_path' => $this->resolveStoredImagePath($technicalSheet?->closure_detail_image),
            'measurements' => $this->buildMeasurementRows($dress),
            'custom_measurements' => $this->buildCustomMeasurementRows($dress),
            'fabrics' => $fabrics,
            'fabric_samples' => array_slice($fabrics, 0, 3),
            'materials' => $this->buildMaterials($dress, $technicalSheet, $fabrics),
            'accessories' => $this->buildAccessories($dress, $technicalSheet),
            'consumption_rows' => $this->buildConsumptionRows($fabrics),
            'description_paragraphs' => $this->splitParagraphs($clientDescription),
            'client_notes_paragraphs' => $this->splitParagraphs((string) ($technicalSheet?->client_notes ?? '')),
            'technical_description_paragraphs' => $this->splitParagraphs($technicalDescription),
            'production_notes' => $this->buildProductionNotes($technicalSheet, $generalNotes),
            'construction_notes' => $this->buildConstructionNotes($technicalSheet, $generalNotes),
            'detail_sections' => $this->buildDetailSections($technicalSheet, $technicalDescription),
            'model_name' => $this->buildModelName($dress, $technicalSheet, $clientDescription),
            'line_name' => $this->buildLineName($dress, $technicalSheet),
            'garment_type' => $this->buildGarmentType($dress, $technicalSheet, $technicalDescription),
            'measurements_responsible' => trim((string) ($technicalSheet?->measurements_responsible ?? '')),
            'nb_notes' => trim((string) ($technicalSheet?->nb_notes ?? '')),
            'closure_details' => trim((string) ($technicalSheet?->closure_details ?? '')),
            'main_fabric' => $mainFabric,
            'sleeve_fabric' => $sleeveFabric,
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, unit: string}>
     */
    private function buildMeasurementRows(Dress $dress): array
    {
        $measurements = $dress->measurements;
        $corset = $dress->corsets->first();

        return collect(DressMeasurement::ORDERED_MEASURES)
            ->map(function (string $label, string $field) use ($measurements, $corset): array {
                $value = $this->resolveMeasurementValue($field, $measurements, $corset);

                return [
                    'label' => $label,
                    'value' => blank($value) ? '' : $this->formatDecimal($value),
                    'unit' => $field === 'inclinazione_spalle' ? 'gradi' : 'cm',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function buildCustomMeasurementRows(Dress $dress): array
    {
        return $dress->customMeasurements
            ->map(function ($measurement): array {
                $value = blank($measurement->value)
                    ? ''
                    : trim($this->formatDecimal($measurement->value) . ' ' . ($measurement->unit ?? ''));

                return [
                    'label' => (string) ($measurement->label ?? 'Misura personalizzata'),
                    'value' => $value,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFabrics(Dress $dress): array
    {
        return $dress->fabrics
            ->map(function ($fabric): array {
                $name = trim((string) ($fabric->name ?? ''));
                $type = trim((string) ($fabric->type ?? ''));
                $colorCode = trim((string) ($fabric->color_code ?? ''));

                return [
                    'name' => $name !== '' ? $name : 'Tessuto senza nome',
                    'type' => $type,
                    'color_code' => $colorCode,
                    'supplier' => trim((string) ($fabric->supplier ?? '')),
                    'meters' => $this->formatDecimal($fabric->meters ?? 0),
                    'purchase_price' => $this->formatCurrencyValue($fabric->purchase_price ?? 0),
                    'client_price' => $this->formatCurrencyValue($fabric->client_price ?? 0),
                    'height' => '-',
                    'photo_absolute_path' => $this->resolveStoredImagePath($fabric->photo_path),
                    'summary' => $this->buildFabricSummary($name, $type, $colorCode),
                ];
            })
            ->values()
            ->all();
    }

    private function buildPrimaryFabricData(
        ?DressTechnicalSheet $technicalSheet,
        array $fabrics,
        int $fallbackIndex,
    ): ?array {
        if ($technicalSheet !== null) {
            $prefix = $fallbackIndex === 0 ? 'main' : 'sleeve';

            $name = trim((string) $technicalSheet->{"{$prefix}_fabric_name"});
            $composition = trim((string) $technicalSheet->{"{$prefix}_fabric_composition"});
            $color = trim((string) $technicalSheet->{"{$prefix}_fabric_color"});

            if ($name !== '' || $composition !== '' || $color !== '') {
                return [
                    'name' => $name,
                    'composition' => $composition,
                    'color' => $color,
                ];
            }
        }

        $fallback = $fabrics[$fallbackIndex] ?? ($fabrics[0] ?? null);

        if ($fallback === null) {
            return null;
        }

        return [
            'name' => $fallback['name'],
            'composition' => $fallback['type'],
            'color' => $fallback['color_code'],
        ];
    }

    private function buildClientDescription(Dress $dress, ?DressTechnicalSheet $technicalSheet): string
    {
        return collect([
            trim((string) ($dress->description ?? '')),
            trim((string) ($technicalSheet?->technical_description ?? '')),
        ])->filter()->implode("\n\n");
    }

    private function buildTechnicalDescription(Dress $dress, ?DressTechnicalSheet $technicalSheet): string
    {
        return collect([
            trim((string) ($technicalSheet?->technical_description ?? '')),
            trim((string) ($dress->description ?? '')),
            trim((string) ($dress->notes ?? '')),
        ])->filter()->implode("\n\n");
    }

    /**
     * @param  array<int, array<string, mixed>>  $fabrics
     * @return array<int, string>
     */
    private function buildMaterials(Dress $dress, ?DressTechnicalSheet $technicalSheet, array $fabrics): array
    {
        $manual = $this->splitLines((string) ($technicalSheet?->materials_notes ?? ''));
        $fallback = collect($fabrics)
            ->map(fn (array $fabric): string => $fabric['summary'])
            ->merge(
                collect($dress->expenses)
                    ->map(fn ($expense): ?string => $this->classifyExpenseAsMaterial($expense->name))
                    ->filter()
            )
            ->values()
            ->all();

        return $this->mergeUniqueTextLists($manual, $fallback);
    }

    /**
     * @return array<int, string>
     */
    private function buildAccessories(Dress $dress, ?DressTechnicalSheet $technicalSheet): array
    {
        $manual = $this->splitLines((string) ($technicalSheet?->accessories_notes ?? ''));

        $fallback = collect($dress->extras)
            ->map(function ($extra): string {
                $description = trim((string) ($extra->description ?? ''));
                $cost = $extra->cost ?? null;

                if ($description === '') {
                    $description = 'Accessorio aggiuntivo';
                }

                if ($cost !== null && (float) $cost > 0) {
                    return sprintf('%s (EUR %s)', $description, $this->formatCurrencyValue($cost));
                }

                return $description;
            })
            ->merge(
                collect($dress->expenses)
                    ->map(fn ($expense): string => trim((string) $expense->name))
                    ->filter(fn (string $name): bool => $this->classifyExpenseAsMaterial($name) === null)
            )
            ->values()
            ->all();

        return $this->mergeUniqueTextLists($manual, $fallback);
    }

    /**
     * @param  array<int, array<string, mixed>>  $fabrics
     * @return array<int, array{fabric: string, height: string, consumption: string}>
     */
    private function buildConsumptionRows(array $fabrics): array
    {
        return collect($fabrics)
            ->map(fn (array $fabric): array => [
                'fabric' => $fabric['summary'],
                'height' => $fabric['height'],
                'consumption' => trim($fabric['meters'] . ' m'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function splitParagraphs(string $text): array
    {
        $paragraphs = preg_split('/\R{2,}/', $text) ?: [];

        return collect($paragraphs)
            ->map(fn (string $paragraph): string => trim($paragraph))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function splitLines(string $text): array
    {
        $items = preg_split('/\R+|\x{2022}+/u', $text) ?: [];

        return collect($items)
            ->map(fn (string $item): string => trim($item, " \t\n\r\0\x0B-"))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $primary
     * @param  array<int, string>  $secondary
     * @return array<int, string>
     */
    private function mergeUniqueTextLists(array $primary, array $secondary): array
    {
        return collect($primary)
            ->merge($secondary)
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function buildProductionNotes(?DressTechnicalSheet $technicalSheet, string $fallbackNotes): array
    {
        $manual = $this->splitLines((string) ($technicalSheet?->production_notes ?? ''));
        $fallback = $this->splitLines($fallbackNotes);

        return $this->mergeUniqueTextLists($manual, $fallback);
    }

    /**
     * @return array<int, string>
     */
    private function buildConstructionNotes(?DressTechnicalSheet $technicalSheet, string $fallbackNotes): array
    {
        $manual = $this->splitLines((string) ($technicalSheet?->construction_notes ?? ''));

        if (! empty($manual)) {
            return $manual;
        }

        return $this->splitLines($fallbackNotes);
    }

    /**
     * @return array<string, array{text: array<int, string>, image_path: ?string}>
     */
    private function buildDetailSections(?DressTechnicalSheet $technicalSheet, string $fallbackText): array
    {
        if ($technicalSheet === null) {
            return $this->buildFallbackDetailSections($fallbackText);
        }

        return [
            'scollo' => [
                'text' => $this->splitLines((string) ($technicalSheet->neckline_details ?? '')),
                'image_path' => $this->resolveStoredImagePath($technicalSheet->neckline_detail_image),
            ],
            'maniche' => [
                'text' => $this->splitLines((string) ($technicalSheet->sleeve_details ?? '')),
                'image_path' => $this->resolveStoredImagePath($technicalSheet->sleeve_detail_image),
            ],
            'corpino' => [
                'text' => $this->splitLines((string) ($technicalSheet->bodice_details ?? '')),
                'image_path' => $this->resolveStoredImagePath($technicalSheet->bodice_detail_image),
            ],
            'dietro' => [
                'text' => $this->splitLines((string) ($technicalSheet->back_details ?? '')),
                'image_path' => $this->resolveStoredImagePath($technicalSheet->back_detail_image),
            ],
            'chiusura' => [
                'text' => $this->splitLines((string) ($technicalSheet->closure_details ?? '')),
                'image_path' => $this->resolveStoredImagePath($technicalSheet->closure_detail_image),
            ],
        ];
    }

    /**
     * @return array<string, array{text: array<int, string>, image_path: ?string}>
     */
    private function buildFallbackDetailSections(string $text): array
    {
        $sentences = collect(preg_split('/(?<=[\.\!\?])\s+/u', $text) ?: [])
            ->map(fn (string $sentence): string => trim($sentence))
            ->filter()
            ->values();

        $keywords = [
            'scollo' => ['scollo', 'collo', 'spalla'],
            'maniche' => ['manic', 'polso', 'gomito'],
            'corpino' => ['corpino', 'busto', 'vita', 'aderent', 'tagli', 'linea'],
            'dietro' => ['dietro', 'zip', 'schiena', 'spacco', 'chiusura'],
            'chiusura' => ['zip', 'chiusura', 'centro dietro'],
        ];

        $sections = [];

        foreach ($keywords as $section => $matches) {
            $sections[$section] = [
                'text' => $sentences
                    ->filter(function (string $sentence) use ($matches): bool {
                        $normalizedSentence = mb_strtolower($sentence);

                        foreach ($matches as $match) {
                            if (str_contains($normalizedSentence, $match)) {
                                return true;
                            }
                        }

                        return false;
                    })
                    ->take(3)
                    ->values()
                    ->all(),
                'image_path' => null,
            ];
        }

        return $sections;
    }

    private function buildModelName(Dress $dress, ?DressTechnicalSheet $technicalSheet, string $description): string
    {
        if (filled($technicalSheet?->model_name)) {
            return (string) $technicalSheet->model_name;
        }

        if ($description !== '') {
            return Str::limit($description, 55, '');
        }

        return 'Abito su misura #' . $dress->id;
    }

    private function buildLineName(Dress $dress, ?DressTechnicalSheet $technicalSheet): string
    {
        if (filled($technicalSheet?->line_name)) {
            return (string) $technicalSheet->line_name;
        }

        if (filled($dress->ceremony_type)) {
            return Str::headline((string) $dress->ceremony_type);
        }

        return 'Su misura';
    }

    private function buildGarmentType(Dress $dress, ?DressTechnicalSheet $technicalSheet, string $description): string
    {
        if (filled($technicalSheet?->garment_type)) {
            return (string) $technicalSheet->garment_type;
        }

        if ($description !== '') {
            return Str::limit($description, 90, '');
        }

        return filled($dress->estimated_time)
            ? 'Abito su misura - ' . $dress->estimated_time
            : 'Abito su misura';
    }

    private function resolvePrimaryDesignImagePath(Dress $dress, ?DressTechnicalSheet $technicalSheet): ?string
    {
        $technicalFront = $this->resolveStoredImagePath($technicalSheet?->front_view_image);

        if ($technicalFront !== null) {
            return $technicalFront;
        }

        foreach (['drawing_image', 'final_image', 'sketch_image'] as $field) {
            $absolutePath = $this->resolveStoredImagePath($dress->{$field} ?? null);

            if ($absolutePath !== null) {
                return $absolutePath;
            }
        }

        return null;
    }

    private function resolveModelCoverImagePath(Dress $dress, ?DressTechnicalSheet $technicalSheet): ?string
    {
        foreach ([
            $dress->final_image ?? null,
            $dress->drawing_image ?? null,
            $dress->sketch_image ?? null,
            $technicalSheet?->front_view_image,
        ] as $path) {
            $absolutePath = $this->resolveStoredImagePath($path);

            if ($absolutePath !== null) {
                return $absolutePath;
            }
        }

        return null;
    }

    private function resolveApprovedFrontImagePath(Dress $dress, ?DressTechnicalSheet $technicalSheet): ?string
    {
        foreach ([
            $technicalSheet?->front_view_image,
            $dress->final_image ?? null,
            $dress->drawing_image ?? null,
            $dress->sketch_image ?? null,
        ] as $path) {
            $absolutePath = $this->resolveStoredImagePath($path);

            if ($absolutePath !== null) {
                return $absolutePath;
            }
        }

        return null;
    }

    private function resolveApprovedBackImagePath(Dress $dress, ?DressTechnicalSheet $technicalSheet): ?string
    {
        foreach ([
            $technicalSheet?->back_view_image,
            $dress->final_image ?? null,
        ] as $path) {
            $absolutePath = $this->resolveStoredImagePath($path);

            if ($absolutePath !== null) {
                return $absolutePath;
            }
        }

        return null;
    }

    private function resolveStoredImagePath(?string $relativePath): ?string
    {
        if (blank($relativePath)) {
            return null;
        }

        $absolutePath = storage_path('app/public/' . ltrim((string) $relativePath, '/'));

        return file_exists($absolutePath) ? $absolutePath : null;
    }

    private function buildFabricSummary(string $name, string $type, string $colorCode): string
    {
        return collect([
            $name !== '' ? $name : 'Tessuto senza nome',
            $type !== '' ? $type : null,
            $colorCode !== '' ? 'Colore ' . $colorCode : null,
        ])->filter()->implode(' - ');
    }

    private function formatDecimal(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    private function formatCurrencyValue(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    private function resolveMeasurementValue(
        string $field,
        ?DressMeasurement $measurements,
        ?DressCorset $corset,
    ): mixed {
        $value = $measurements?->{$field};

        if ($field === 'lunghezza_ginocchio' && blank($value)) {
            $value = $measurements?->altezza_ginocchio;
        }

        if (! blank($value)) {
            return $value;
        }

        if (! $corset) {
            return $value;
        }

        return match ($field) {
            'seno' => $corset->circonferenza_seno,
            'sotto_seno' => $corset->circonferenza_sotto_seno,
            'vita' => $corset->circonferenza_vita,
            'bacino' => $measurements?->fianchi ?? $corset->circonferenza_fianchi_15_cm,
            'lunghezza_seno' => $corset->altezza_seno,
            default => $value,
        };
    }

    private function classifyExpenseAsMaterial(?string $name): ?string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $normalized = mb_strtolower($name);
        $materialKeywords = ['fodera', 'nastro', 'filo', 'termo', 'tela', 'rinforzo'];

        foreach ($materialKeywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return $name;
            }
        }

        return null;
    }
}
