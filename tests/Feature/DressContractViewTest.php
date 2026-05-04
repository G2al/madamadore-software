<?php

namespace Tests\Feature;

use App\Models\Dress;
use App\Models\DressExpense;
use App\Models\DressExtra;
use App\Models\DressFabric;
use App\Models\DressTechnicalSheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DressContractViewTest extends TestCase
{
    private string $photoPath = 'dress-fabrics/test-preventivo-photo.jpg';

    protected function setUp(): void
    {
        parent::setUp();

        File::ensureDirectoryExists(dirname($this->absolutePhotoPath()));
        File::put($this->absolutePhotoPath(), 'fake-image-content');
    }

    protected function tearDown(): void
    {
        File::delete($this->absolutePhotoPath());

        parent::tearDown();
    }

    public function test_preventivo_cliente_hides_supplier_shows_fabric_photo_and_keeps_four_pages(): void
    {
        $dress = new Dress([
            'customer_name' => 'Chiara Test',
            'phone_number' => '3331234567',
            'notes' => 'Preventivo con campione tessuto.',
            'description' => 'Abito lungo con corpino sagomato.',
            'deposit' => 120,
            'remaining' => 180,
            'manufacturing_price' => 0,
        ]);

        $dress->id = 77;
        $dress->delivery_date = Carbon::parse('2026-05-20');
        $dress->setRelation('measurements', null);
        $dress->setRelation('customMeasurements', new Collection());
        $dress->setRelation('corsets', new Collection());
        $dress->setRelation('technicalSheet', new DressTechnicalSheet([
            'client_notes' => "Cliente vuole linea pulita\nPriorita alla vestibilita",
            'front_view_image' => null,
            'back_view_image' => null,
        ]));
        $dress->setRelation('extras', collect([
            new DressExtra([
                'description' => 'Applicazione pizzo',
                'cost' => 35,
            ]),
        ]));
        $dress->setRelation('expenses', collect([
            new DressExpense([
                'name' => 'Zip invisibile',
                'price' => 12,
            ]),
        ]));
        $dress->setRelation('fabrics', collect([
            new DressFabric([
                'name' => 'Mikado',
                'type' => 'Seta',
                'meters' => 3.5,
                'purchase_price' => 20,
                'client_price' => 60,
                'supplier' => 'Fornitore Riservato',
                'color_code' => 'AV01',
                'photo_path' => $this->photoPath,
            ]),
        ]));

        $html = view('pdf.dress-preventivo', [
            'dress' => $dress,
            'document' => app(\App\Services\DressPdfDataService::class)->build($dress),
        ])->render();

        $this->assertStringNotContainsString('Fornitore Riservato', $html);
        $this->assertStringContainsString('Scheda cliente', $html);
        $this->assertStringContainsString('Abito Definitivo', $html);
        $this->assertStringContainsString('Contratto di Vendita', $html);
        $this->assertStringContainsString('Riepilogo Economico', $html);
        $this->assertStringContainsString('Campioni tessuto', $html);
        $this->assertStringContainsString($this->absolutePhotoPath(), $html);
        $this->assertSame(3, substr_count($html, '<div class="page-break">'));
    }

    private function absolutePhotoPath(): string
    {
        return storage_path('app/public/' . $this->photoPath);
    }
}
