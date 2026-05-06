<?php

namespace App\Services;

use App\Models\DressFabric;
use App\Models\ShoppingItem;
use Illuminate\Support\Collection;

class UnifiedShoppingListPdfService
{
    public function buildUnified(): array
    {
        $manualItems = $this->normalizeManualItems(
            ShoppingItem::query()
                ->whereNull('purchase_date')
                ->orderBy('supplier')
                ->orderBy('name')
                ->orderBy('color_code')
                ->get(),
        );

        $automaticItems = $this->normalizeAutomaticItems(
            DressFabric::query()
                ->pendingPurchase()
                ->with('dress:id,customer_name')
                ->orderBy('supplier')
                ->orderBy('name')
                ->orderBy('color_code')
                ->get(),
        );

        return $this->buildPayload(
            $manualItems->merge($automaticItems),
            'Lista della Spesa Unica',
            'Voci manuali e tessuti automatici in un unico riepilogo per fornitore',
        );
    }

    public function buildManualOnly(array $shoppingItemIds): array
    {
        $manualItems = $this->normalizeManualItems(
            ShoppingItem::query()
                ->whereIn('id', $shoppingItemIds)
                ->orderBy('supplier')
                ->orderBy('name')
                ->orderBy('color_code')
                ->get(),
        );

        return $this->buildPayload(
            $manualItems,
            'Lista della Spesa',
            'Voci manuali selezionate',
        );
    }

    /**
     * @param  Collection<int, ShoppingItem>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function normalizeManualItems(Collection $items): Collection
    {
        return $items->map(function (ShoppingItem $item): array {
            $quantity = $item->quantity !== null ? (float) $item->quantity : null;
            $price = $item->price !== null ? (float) $item->price : null;

            return [
                'supplier' => $this->normalizeSupplier($item->supplier),
                'group_name' => $item->name ?: 'Articolo senza nome',
                'group_subtitle' => null,
                'variant' => $item->color_code ?: 'Senza codice',
                'quantity' => $quantity,
                'unit' => $item->unit_type === 'metri' ? 'mt' : 'pz',
                'price' => $price,
                'subtotal' => $this->calculateManualSubtotal($quantity, $price),
                'photo_path' => $item->photo_path,
                'purchase_label' => $item->purchase_date?->format('d/m/Y') ?? 'Non saldato',
            ];
        });
    }

    /**
     * @param  Collection<int, DressFabric>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function normalizeAutomaticItems(Collection $items): Collection
    {
        return $items->map(function (DressFabric $item): array {
            $quantity = $item->meters !== null ? (float) $item->meters : null;
            $price = $item->purchase_price !== null ? (float) $item->purchase_price : null;

            return [
                'supplier' => $this->normalizeSupplier($item->supplier),
                'group_name' => $item->name ?: 'Tessuto senza nome',
                'group_subtitle' => $item->type ?: null,
                'variant' => $item->color_code ?: 'Senza codice',
                'quantity' => $quantity,
                'unit' => 'mt',
                'price' => $price,
                'subtotal' => ($quantity ?? 0.0) * ($price ?? 0.0),
                'photo_path' => $item->photo_path,
                'purchase_label' => 'Non saldato',
            ];
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildPayload(Collection $rows, string $title, string $subtitle): array
    {
        $supplierGroups = $rows
            ->groupBy('supplier')
            ->map(function (Collection $supplierRows, string $supplierName): array {
                $aggregatedRows = $this->aggregateRows($supplierRows)->values();

                return [
                    'name' => $supplierName,
                    'rows' => $aggregatedRows->all(),
                    'unit_totals' => $this->buildUnitTotals($aggregatedRows),
                    'total_cost' => (float) $aggregatedRows->sum('subtotal'),
                    'total_rows' => $aggregatedRows->count(),
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'supplierGroups' => $supplierGroups,
            'overallTotalCost' => (float) $rows->sum('subtotal'),
            'overallRows' => $rows->count(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function aggregateRows(Collection $rows): Collection
    {
        return $rows
            ->groupBy(function (array $row): string {
                $priceKey = $row['price'] === null ? 'null' : number_format((float) $row['price'], 4, '.', '');

                return implode('|', [
                    mb_strtolower((string) $row['group_name']),
                    mb_strtolower((string) $row['variant']),
                    $row['unit'],
                    $priceKey,
                ]);
            })
            ->map(function (Collection $groupRows): array {
                $hasQuantity = $groupRows->contains(fn (array $row): bool => $row['quantity'] !== null);

                return [
                    'name' => (string) $groupRows->first()['group_name'],
                    'subtitle' => $groupRows
                        ->pluck('group_subtitle')
                        ->filter()
                        ->unique()
                        ->values()
                        ->join(' / '),
                    'variant' => (string) $groupRows->first()['variant'],
                    'quantity' => $hasQuantity ? (float) $groupRows->sum(fn (array $row): float => (float) ($row['quantity'] ?? 0)) : null,
                    'unit' => (string) $groupRows->first()['unit'],
                    'price' => $groupRows->first()['price'] !== null
                        ? (float) $groupRows->first()['price']
                        : null,
                    'subtotal' => (float) $groupRows->sum('subtotal'),
                    'photo_path' => $groupRows->pluck('photo_path')->filter()->first(),
                    'purchase_label' => (string) ($groupRows->first()['purchase_label'] ?? 'Non saldato'),
                    'supplier' => (string) $groupRows->first()['supplier'],
                ];
            })
            ->sortBy(fn (array $row) => mb_strtolower($row['name'] . '|' . $row['variant']), SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<int, array{unit: string, quantity: float}>
     */
    private function buildUnitTotals(Collection $rows): array
    {
        return $rows
            ->filter(fn (array $row): bool => $row['quantity'] !== null)
            ->groupBy('unit')
            ->map(function (Collection $unitRows, string $unit): array {
                return [
                    'unit' => $unit,
                    'quantity' => (float) $unitRows->sum(fn (array $row): float => (float) ($row['quantity'] ?? 0)),
                ];
            })
            ->values()
            ->all();
    }

    private function calculateManualSubtotal(?float $quantity, ?float $price): float
    {
        if ($price === null) {
            return 0.0;
        }

        if ($quantity === null) {
            return $price;
        }

        return $quantity * $price;
    }

    private function normalizeSupplier(?string $supplier): string
    {
        $supplier = trim((string) $supplier);

        return $supplier !== '' ? $supplier : 'Senza fornitore';
    }
}
