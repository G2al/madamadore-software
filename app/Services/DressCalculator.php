<?php

namespace App\Services;

class DressCalculator
{
    /**
     * Calcola i valori economici a partire da fabrics, extras e deposito.
     *
     * @param array $fabrics
     * @param array $extras
     * @param float $deposit
     * @return array
     */
    public static function calculate(array $fabrics, array $extras, float $deposit = 0): array
    {
        $totalPurchaseCost = 0.0;
        $totalFabricClientPrice = 0.0;

        foreach ($fabrics as $fabric) {
            $meters        = (float) ($fabric['meters'] ?? 0);
            $purchasePrice = (float) ($fabric['purchase_price'] ?? 0);
            $clientPrice   = (float) ($fabric['client_price'] ?? 0);

            $totalPurchaseCost      += $meters * $purchasePrice;
            $totalFabricClientPrice += $meters * $clientPrice;
        }

        $totalExtras = collect($extras)->sum(fn ($extra) => (float) ($extra['cost'] ?? 0));

        $totalClientPrice = $totalFabricClientPrice + $totalExtras;
        $profit           = $totalClientPrice - $totalPurchaseCost;
        $remaining        = $totalClientPrice - $deposit;

        return [
            'total_purchase_cost' => $totalPurchaseCost,
            'total_client_price'  => $totalClientPrice,
            'total_profit'        => $profit,
            'remaining_balance'   => $remaining,
        ];
    }
}
