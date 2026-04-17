<?php

namespace Tests\Feature;

use App\Models\Dress;
use App\Models\DressFabric;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FabricPdfViewTest extends TestCase
{
    public function test_fabric_pdf_is_grouped_by_supplier_then_fabric_with_color_totals(): void
    {
        $fabrics = new Collection([
            $this->makeFabric('Casdit', 'Candace', 'Tulle', 'X1', 1.0, 60.0),
            $this->makeFabric('Casdit', 'Candace', 'Tulle', 'X2', 3.0, 60.0),
            $this->makeFabric('Casdit', 'Candace', 'Tulle', 'X2', 2.0, 60.0),
            $this->makeFabric('Casdit', 'Organza', 'Seta', 'Y1', 4.0, 40.0),
        ]);

        $html = view('pdf.fabric-requirements', [
            'fabrics' => $fabrics,
            'totalCost' => $fabrics->sum(fn (DressFabric $fabric) => (float) $fabric->meters * (float) $fabric->purchase_price),
            'generatedAt' => '17/04/2026 10:00',
            'colorCode' => null,
        ])->render();

        $this->assertStringContainsString('FORNITORE: CASDIT', $html);
        $this->assertStringContainsString('TESSUTO: CANDACE', $html);
        $this->assertStringContainsString('TESSUTO: ORGANZA', $html);
        $this->assertStringContainsString('X1', $html);
        $this->assertStringContainsString('X2', $html);
        $this->assertStringContainsString('5,00 mt', $html);
        $this->assertStringNotContainsString('CLIENTE/ABITO', $html);
        $this->assertStringNotContainsString('URGENZA', $html);
    }

    private function makeFabric(
        string $supplier,
        string $name,
        string $type,
        string $colorCode,
        float $meters,
        float $purchasePrice,
    ): DressFabric {
        $fabric = new DressFabric([
            'supplier' => $supplier,
            'name' => $name,
            'type' => $type,
            'color_code' => $colorCode,
            'meters' => $meters,
            'purchase_price' => $purchasePrice,
        ]);

        $fabric->setRelation('dress', new Dress([
            'customer_name' => 'Cliente Test',
        ]));

        return $fabric;
    }
}
