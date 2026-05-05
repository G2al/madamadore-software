<?php

namespace Tests\Feature;

use App\Models\Dress;
use App\Models\DressExpense;
use App\Models\DressExtra;
use App\Models\DressFabric;
use App\Models\DressMeasurement;
use App\Models\DressTechnicalSheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DressReceiptViewTest extends TestCase
{
    public function test_modellino_completo_contains_overview_production_and_technical_pages(): void
    {
        $dress = $this->makeDress();

        $html = view('pdf.dress-receipt', [
            'dress' => $dress,
            'document' => app(\App\Services\DressPdfDataService::class)->build($dress),
        ])->render();

        $this->assertStringContainsString('Scheda cliente', $html);
        $this->assertStringContainsString('Modellino Abito', $html);
        $this->assertStringContainsString('Scheda Produzione', $html);
        $this->assertStringContainsString('Scheda Tecnica', $html);
        $this->assertSame(3, substr_count($html, '<div class="page-break">'));
    }

    public function test_single_internal_sheets_render_expected_sections(): void
    {
        $dress = $this->makeDress();
        $document = app(\App\Services\DressPdfDataService::class)->build($dress);

        $productionHtml = view('pdf.dress-model-production', [
            'dress' => $dress,
            'document' => $document,
        ])->render();

        $technicalHtml = view('pdf.dress-model-technical', [
            'dress' => $dress,
            'document' => $document,
        ])->render();

        $this->assertStringContainsString('Consumo tessuti', $productionHtml);
        $this->assertStringContainsString('Campioni tessuto', $productionHtml);
        $this->assertStringContainsString('Dettaglio scollo', $technicalHtml);
        $this->assertStringContainsString('Respons. misure', $technicalHtml);
    }

    private function makeDress(): Dress
    {
        $dress = new Dress([
            'customer_name' => 'Maria Rossi',
            'phone_number' => '3331112233',
            'notes' => "Prima prova corpino\nControllare lunghezza finale",
            'deposit' => 200,
            'remaining' => 350,
            'manufacturing_price' => 0,
        ]);

        $dress->id = 15;
        $dress->description = 'Abito lungo con manica ampia e corpino sagomato.';
        $dress->ceremony_type = 'cerimonia';
        $dress->ceremony_holder = 'Maria Rossi';
        $dress->ceremony_date = Carbon::parse('2026-06-10');
        $dress->delivery_date = Carbon::parse('2026-06-25');

        $dress->setRelation('measurements', new DressMeasurement([
            'circonferenza_collo' => 36,
            'torace' => 88,
            'seno' => 92,
            'vita' => 72,
            'bacino' => 98,
            'lunghezza_abito' => 145,
            'lunghezza_manica' => 61,
        ]));
        $dress->setRelation('customMeasurements', new Collection());
        $dress->setRelation('corsets', new Collection());
        $dress->setRelation('technicalSheet', new DressTechnicalSheet([
            'model_name' => 'Abito colonna demo',
            'line_name' => 'Cerimonia',
            'garment_type' => 'Abito lungo con manica ampia',
            'technical_description' => "Abito lungo con corpino sagomato.\n\nManiche leggere in organza.",
            'production_notes' => "Prima prova corpino\nControllare lunghezza finale",
            'construction_notes' => "Tagli princesse\nZip invisibile dietro",
            'neckline_details' => 'Scollo a cuore morbido',
            'sleeve_details' => 'Manica ampia con polso alto',
            'bodice_details' => 'Corpino strutturato',
            'back_details' => 'Spacco centrale',
            'closure_details' => 'Zip invisibile dietro',
            'measurements_responsible' => 'Dora Maione',
            'nb_notes' => 'Controllare prova finale con scarpa.',
            'main_fabric_name' => 'Crepe cady',
            'main_fabric_composition' => 'Tessuto principale',
            'main_fabric_color' => 'AV01',
            'sleeve_fabric_name' => 'Organza',
            'sleeve_fabric_composition' => 'Manica',
            'sleeve_fabric_color' => 'ORG11',
        ]));
        $dress->setRelation('extras', collect([
            new DressExtra([
                'description' => 'Bottoni rivestiti',
                'cost' => 18,
            ]),
        ]));
        $dress->setRelation('expenses', collect([
            new DressExpense([
                'name' => 'Nastro stabilizzante',
                'price' => 6,
            ]),
        ]));
        $dress->setRelation('fabrics', collect([
            new DressFabric([
                'name' => 'Crepe cady',
                'type' => 'Tessuto principale',
                'meters' => 2.8,
                'purchase_price' => 22,
                'client_price' => 58,
                'color_code' => 'AV01',
            ]),
            new DressFabric([
                'name' => 'Organza',
                'type' => 'Manica',
                'meters' => 1.2,
                'purchase_price' => 14,
                'client_price' => 35,
                'color_code' => 'ORG11',
            ]),
        ]));

        return $dress;
    }
}
