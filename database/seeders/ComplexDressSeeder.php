<?php

namespace Database\Seeders;

use App\Models\Dress;
use App\Models\DressCorset;
use App\Models\DressCustomMeasurement;
use App\Models\DressExpense;
use App\Models\DressExtra;
use App\Models\DressFabric;
use App\Models\DressMeasurement;
use App\Models\DressTechnicalSheet;
use App\Models\Fabric;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ComplexDressSeeder extends Seeder
{
    private const ASSET_DIRECTORY = 'seeders/complex-dress';

    public function run(): void
    {
        $assets = $this->generateDemoAssets();

        DB::transaction(function () use ($assets): void {
            $dress = $this->createOrUpdateDress($assets);

            $dress->measurements()->delete();
            $dress->customMeasurements()->delete();
            $dress->fabrics()->delete();
            $dress->extras()->delete();
            $dress->expenses()->delete();
            $dress->corsets()->delete();
            $dress->technicalSheet()?->delete();

            $this->seedMeasurements($dress);
            $this->seedCustomMeasurements($dress);
            $this->seedFabrics($dress, $assets);
            $this->seedExtras($dress);
            $this->seedExpenses($dress);
            $this->seedCorset($dress);
            $this->seedTechnicalSheet($dress, $assets);

            $dress->load('fabrics', 'extras');
            $dress->recalcFinancials(true);

            $this->command?->info("Abito complesso creato/aggiornato con ID {$dress->id} per {$dress->customer_name}.");
        }, 5);
    }

    private function createOrUpdateDress(array $assets): Dress
    {
        $attributes = $this->onlyExistingColumns('dresses', [
            'customer_name' => 'Cliente Seeder Demo',
            'phone_number' => '3334242424',
            'ceremony_date' => '2026-09-12',
            'ceremony_type' => 'matrimonio',
            'ceremony_holder' => 'Giulia Ricci',
            'delivery_date' => '2026-08-30',
            'description' => 'Abito lungo da cerimonia con corpino strutturato, scollo a cuore morbido, maniche in organza e linea slanciata con spacco centrale dietro.',
            'notes' => implode("\n", [
                'Ispirazione elegante con vita segnata e proporzioni pulite.',
                'Controllare la vestibilita del corpino prima del montaggio definitivo delle maniche.',
                'Rifinire lo spacco dietro con invisibile interna e margine di sicurezza.',
                'Coordinare il colore del filo con tessuto principale e organza.',
            ]),
            'estimated_time' => '160 ore di lavorazione',
            'manufacturing_price' => 950,
            'deposit' => 700,
            'status' => 'in_lavorazione',
            'manual_client_price' => 0,
            'use_manual_price' => false,
            'payment_method' => 'bonifico',
            'ritirato' => false,
            'saldato' => false,
            'fabric_bought_at' => '2026-05-12 10:30:00',
            'cut_completed_at' => '2026-06-03 16:45:00',
            'final_measurements_notes' => 'Ultimo controllo vestibilita effettuato con scarpa da 9 cm.',
            'pronta_misura_notes' => 'Versione atelier adattata su base sartoriale interna.',
            'sketch_image' => $assets['design_overview'],
            'final_image' => $assets['design_overview'],
            'drawing_image' => $assets['design_overview'],
            'drawing_pad' => null,
            'archived_at' => null,
        ]);

        $dress = Dress::query()->firstOrNew([
            'phone_number' => '3334242424',
        ]);

        $dress->forceFill($attributes);
        $dress->save();

        return $dress;
    }

    private function seedMeasurements(Dress $dress): void
    {
        $measurements = $this->onlyExistingColumns('dress_measurements', [
            'dress_id' => $dress->id,
            'spalle' => 40.0,
            'torace' => 88.0,
            'sotto_seno' => 77.0,
            'vita' => 68.0,
            'fianchi' => 98.0,
            'lunghezza_busto' => 42.5,
            'lunghezza_manica' => 61.0,
            'circonferenza_braccio' => 28.0,
            'circonferenza_polso' => 16.0,
            'altezza_totale' => 175.0,
            'lunghezza_abito' => 153.0,
            'lunghezza_gonna' => 113.0,
            'circonferenza_collo' => 35.0,
            'larghezza_schiena' => 37.5,
            'altezza_seno' => 28.0,
            'distanza_seni' => 18.0,
            'circonferenza_coscia' => 56.0,
            'lunghezza_cavallo' => 28.5,
            'altezza_ginocchio' => 58.0,
            'circonferenza_caviglia' => 22.0,
            'seno' => 91.0,
            'bacino' => 98.0,
            'lunghezza_bacino' => 21.0,
            'lunghezza_seno' => 27.5,
            'precisapince' => 3.2,
            'scollo' => 19.0,
            'scollo_dietro' => 12.0,
            'lunghezza_vita' => 42.0,
            'lunghezza_vita_dietro' => 41.0,
            'inclinazione_spalle' => 18.0,
            'larghezza_torace_interno' => 31.0,
            'lunghezza_taglio' => 46.0,
            'lunghezza_gonna_avanti' => 110.0,
            'lunghezza_gonna_dietro' => 114.0,
            'lunghezza_gomito' => 34.0,
            'livello_ascellare' => 19.0,
            'lunghezza_pantalone_interno' => 82.0,
            'lunghezza_pantalone_esterno' => 109.0,
            'lunghezza_ginocchio' => 58.0,
            'circonferenza_ginocchio' => 38.0,
            'circonferenza_taglio' => 44.0,
        ]);

        DressMeasurement::query()->create($measurements);
    }

    private function seedCustomMeasurements(Dress $dress): void
    {
        $rows = [
            [
                'label' => 'Altezza tacco prova',
                'value' => 9.0,
                'unit' => 'cm',
                'notes' => 'Scarpa usata per tutte le prove finali.',
            ],
            [
                'label' => 'Profondita coppa destra',
                'value' => 11.5,
                'unit' => 'cm',
                'notes' => 'Da verificare dopo montaggio interno bustier.',
            ],
            [
                'label' => 'Larghezza spacco dietro',
                'value' => 22.0,
                'unit' => 'cm',
                'notes' => 'Spacco centrale per migliorare la camminata.',
            ],
        ];

        foreach ($rows as $row) {
            DressCustomMeasurement::query()->create(
                $this->onlyExistingColumns('dress_custom_measurements', [
                    'dress_id' => $dress->id,
                    ...$row,
                ])
            );
        }
    }

    private function seedFabrics(Dress $dress, array $assets): void
    {
        $inventoryFabrics = [
            [
                'name' => 'Crepe Cady Luxury',
                'type' => 'Tessuto principale',
                'color_code' => 'IVR-101',
                'supplier' => 'Casdit',
                'purchase_price' => 48.00,
                'client_price' => 115.00,
                'image' => $assets['swatches'][0],
                'meters' => 3.80,
                'photo_path' => $assets['swatches'][0],
            ],
            [
                'name' => 'Organza Seta Soft',
                'type' => 'Maniche',
                'color_code' => 'ORG-204',
                'supplier' => 'Casdit Group',
                'purchase_price' => 28.00,
                'client_price' => 76.00,
                'image' => $assets['swatches'][1],
                'meters' => 1.60,
                'photo_path' => $assets['swatches'][1],
            ],
            [
                'name' => 'Fodera Satin Stretch',
                'type' => 'Fodera interna',
                'color_code' => 'LIN-032',
                'supplier' => 'Tessilan',
                'purchase_price' => 14.50,
                'client_price' => 39.00,
                'image' => $assets['swatches'][2],
                'meters' => 2.40,
                'photo_path' => $assets['swatches'][2],
            ],
        ];

        foreach ($inventoryFabrics as $fabricData) {
            $fabric = Fabric::query()->firstOrCreate(
                ['name' => $fabricData['name'], 'color_code' => $fabricData['color_code']],
                $this->onlyExistingColumns('fabrics', $fabricData)
            );

            DressFabric::query()->create(
                $this->onlyExistingColumns('dress_fabrics', [
                    'dress_id' => $dress->id,
                    'fabric_id' => $fabric->id,
                    'name' => $fabricData['name'],
                    'type' => $fabricData['type'],
                    'meters' => $fabricData['meters'],
                    'purchase_price' => $fabricData['purchase_price'],
                    'client_price' => $fabricData['client_price'],
                    'color_code' => $fabricData['color_code'],
                    'supplier' => $fabricData['supplier'],
                    'photo_path' => $fabricData['photo_path'],
                ])
            );
        }
    }

    private function seedExtras(Dress $dress): void
    {
        $extras = [
            ['description' => 'Ricamo cintura con punti luce', 'cost' => 180.00],
            ['description' => 'Bottoni rivestiti su polsino', 'cost' => 65.00],
            ['description' => 'Bustier interno steccato', 'cost' => 140.00],
            ['description' => 'Coda leggera removibile', 'cost' => 220.00],
        ];

        foreach ($extras as $extra) {
            DressExtra::query()->create(
                $this->onlyExistingColumns('dress_extras', [
                    'dress_id' => $dress->id,
                    ...$extra,
                ])
            );
        }
    }

    private function seedExpenses(Dress $dress): void
    {
        $expenses = [
            ['name' => 'Cerniera invisibile premium 60 cm', 'price' => 9.50, 'photo_path' => null],
            ['name' => 'Coppette interne modellanti', 'price' => 16.00, 'photo_path' => null],
            ['name' => 'Nastro stabilizzante scollo', 'price' => 6.50, 'photo_path' => null],
            ['name' => 'Filo seta avorio coordinato', 'price' => 7.00, 'photo_path' => null],
        ];

        foreach ($expenses as $expense) {
            DressExpense::query()->create(
                $this->onlyExistingColumns('dress_expenses', [
                    'dress_id' => $dress->id,
                    ...$expense,
                ])
            );
        }
    }

    private function seedCorset(Dress $dress): void
    {
        DressCorset::query()->create(
            $this->onlyExistingColumns('dress_corsets', [
                'dress_id' => $dress->id,
                'circonferenza_seno' => 91.0,
                'circonferenza_sotto_seno' => 77.0,
                'circonferenza_vita' => 68.0,
                'circonferenza_fianchi_15_cm' => 88.0,
                'altezza_laterale' => 19.0,
                'arco_orizzontale' => 35.0,
                'altezza_seno' => 27.5,
                'linea_sotto_seno' => 9.8,
                'raggio_inferiore' => 14.5,
                'ripresa_vita_davanti' => 2.0,
                'ripresa_vita_lato' => 2.8,
                'ripresa_vita_dietro' => 1.7,
                'ripresa_fianchi_davanti' => 1.1,
                'ripresa_fianchi_lato' => 1.9,
                'ripresa_fianchi_dietro' => 1.0,
            ])
        );
    }

    private function seedTechnicalSheet(Dress $dress, array $assets): void
    {
        if (! Schema::hasTable('dress_technical_sheets')) {
            return;
        }

        DressTechnicalSheet::query()->create(
            $this->onlyExistingColumns('dress_technical_sheets', [
                'dress_id' => $dress->id,
                'model_name' => 'Abito colonna con manica vaporosa',
                'line_name' => 'Elegante / Cerimonia',
                'garment_type' => 'Abito lungo aderente con corpino strutturato e maniche in organza',
                'client_notes' => implode("\n", [
                    'La cliente desidera un effetto slanciato e pulito sul davanti.',
                    'Priorita alla comodita in camminata e seduta.',
                    'Richiesta una resa fotografica luminosa ma non eccessiva.',
                ]),
                'technical_description' => implode("\n\n", [
                    'Abito lungo aderente con linea a colonna, corpino modellato e punto vita definito.',
                    'Scollo a cuore morbido con sostegno interno e maniche ampie in organza alleggerita, chiuse su polso strutturato con bottoni rivestiti.',
                    'Dietro con zip invisibile centrale e spacco di servizio per facilitare il movimento.',
                ]),
                'production_notes' => implode("\n", [
                    'Cartamodello base taglia 40 adattato sulle misure effettive cliente.',
                    'Verificare prima prova del corpino prima del taglio della fodera definitiva.',
                    'Stabilizzare scollo e spalle con nastro leggero interno.',
                    'Tenere margine abbondante sul fondo per eventuale regolazione tacco.',
                    'Controllare simmetria arricciatura maniche prima di chiudere il polso.',
                ]),
                'construction_notes' => implode("\n", [
                    'Scollo con curva morbida e sostegno invisibile interno.',
                    'Manica in organza leggermente arricciata su testa e fondo.',
                    'Corpino con tagli princesse e sostegno bustier nascosto.',
                    'Dietro con zip invisibile e spacco rifinito.',
                ]),
                'materials_notes' => implode("\n", [
                    'Crepe cady luxury avorio per il corpo principale.',
                    'Organza seta soft per entrambe le maniche.',
                    'Fodera satin stretch leggera per interno corpino e gonna.',
                    'Nastro stabilizzante leggero per scollo e spalle.',
                    'Filo coordinato avorio e supporti interni bustier.',
                ]),
                'accessories_notes' => implode("\n", [
                    'Cerniera invisibile premium 60 cm.',
                    'Bottoni rivestiti su polso alto.',
                    'Coppette interne modellanti.',
                    'Ricamo cintura con punti luce discreti.',
                ]),
                'measurements_responsible' => 'Dora Maione',
                'nb_notes' => 'Controllare la lunghezza finale con scarpa da 9 cm e confermare l apertura dello spacco in prova.',
                'neckline_details' => 'Scollo a cuore morbido con centro leggermente rialzato e linea pulita senza eccessi.',
                'sleeve_details' => 'Manica lunga in organza velata con volume soffice e polso strutturato alto.',
                'bodice_details' => 'Corpino aderente con tagli princesse, sostegno interno e punto vita definito.',
                'back_details' => 'Centro dietro pulito con zip invisibile e spacco di servizio lineare.',
                'closure_details' => 'Zip invisibile centrale dietro con rinforzo interno e chiusura pulita.',
                'main_fabric_name' => 'Crepe Cady Luxury',
                'main_fabric_composition' => 'Crepe cady strutturato',
                'main_fabric_color' => 'Avorio chiaro',
                'sleeve_fabric_name' => 'Organza Seta Soft',
                'sleeve_fabric_composition' => 'Organza leggera velata',
                'sleeve_fabric_color' => 'Avorio trasparente',
                'front_view_image' => $assets['front_view'],
                'back_view_image' => $assets['back_view'],
                'neckline_detail_image' => $assets['neckline_detail'],
                'sleeve_detail_image' => $assets['sleeve_detail'],
                'bodice_detail_image' => $assets['bodice_detail'],
                'back_detail_image' => $assets['back_detail'],
                'closure_detail_image' => $assets['closure_detail'],
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function generateDemoAssets(): array
    {
        $directory = storage_path('app/public/' . self::ASSET_DIRECTORY);
        File::ensureDirectoryExists($directory);

        $assets = [
            'design_overview' => $this->relativeAssetPath('design-overview.svg'),
            'front_view' => $this->relativeAssetPath('front-view.svg'),
            'back_view' => $this->relativeAssetPath('back-view.svg'),
            'neckline_detail' => $this->relativeAssetPath('neckline-detail.svg'),
            'sleeve_detail' => $this->relativeAssetPath('sleeve-detail.svg'),
            'bodice_detail' => $this->relativeAssetPath('bodice-detail.svg'),
            'back_detail' => $this->relativeAssetPath('back-detail.svg'),
            'closure_detail' => $this->relativeAssetPath('closure-detail.svg'),
            'swatches' => [
                $this->relativeAssetPath('swatch-main.svg'),
                $this->relativeAssetPath('swatch-sleeve.svg'),
                $this->relativeAssetPath('swatch-lining.svg'),
            ],
        ];

        $this->createOverviewSvg($this->absoluteAssetPath($assets['design_overview']));
        $this->createDressViewSvg($this->absoluteAssetPath($assets['front_view']), 'DAVANTI');
        $this->createDressViewSvg($this->absoluteAssetPath($assets['back_view']), 'DIETRO');
        $this->createDetailSvg($this->absoluteAssetPath($assets['neckline_detail']), 'Dettaglio scollo', ['Scollo a cuore', 'curva morbida', 'centro pulito'], '#d9e8f5');
        $this->createDetailSvg($this->absoluteAssetPath($assets['sleeve_detail']), 'Dettaglio manica', ['Organza velata', 'arricciatura morbida', 'polso alto'], '#eff4fb');
        $this->createDetailSvg($this->absoluteAssetPath($assets['bodice_detail']), 'Dettaglio corpino', ['Tagli princesse', 'sostegno interno', 'vita definita'], '#f5eadf');
        $this->createDetailSvg($this->absoluteAssetPath($assets['back_detail']), 'Dettaglio dietro', ['Spacco centrale', 'linea pulita', 'caduta regolare'], '#f1ece8');
        $this->createDetailSvg($this->absoluteAssetPath($assets['closure_detail']), 'Chiusura', ['Zip invisibile', 'centro dietro', 'rifinitura interna'], '#f7f3ef');

        $this->createSwatchSvg($this->absoluteAssetPath($assets['swatches'][0]), 'Crepe Cady', '#e6dccf', '#d6c8b6');
        $this->createSwatchSvg($this->absoluteAssetPath($assets['swatches'][1]), 'Organza', '#e7eef6', '#d4e0ef');
        $this->createSwatchSvg($this->absoluteAssetPath($assets['swatches'][2]), 'Fodera', '#e8e2dc', '#d8cec5');

        return $assets;
    }

    private function createOverviewSvg(string $path): void
    {
        $frontFigure = $this->dressFigureSvg(350, 210, false);
        $backFigure = $this->dressFigureSvg(720, 210, true);

        File::put($path, <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="1700" viewBox="0 0 1200 1700">
  <rect width="1200" height="1700" fill="#ffffff"/>
  <rect x="30" y="30" width="1140" height="1640" fill="none" stroke="#d7c7b9" stroke-width="3"/>
  <text x="600" y="88" text-anchor="middle" font-size="34" font-family="Arial" fill="#5a4d45">ABITO DIMOSTRATIVO - VISTA COMPLETA</text>
  {$frontFigure}
  {$backFigure}
  <text x="485" y="1440" text-anchor="middle" font-size="28" font-family="Arial" fill="#5a4d45">DAVANTI</text>
  <text x="855" y="1440" text-anchor="middle" font-size="28" font-family="Arial" fill="#5a4d45">DIETRO</text>
  <text x="600" y="1530" text-anchor="middle" font-size="24" font-family="Arial" fill="#5a4d45">Linea a colonna · maniche in organza · corpino strutturato</text>
  <text x="600" y="1580" text-anchor="middle" font-size="22" font-family="Arial" fill="#7a6f68">Seeder demo per test completo delle schede PDF Dora</text>
</svg>
SVG);
    }

    private function createDressViewSvg(string $path, string $label): void
    {
        $isBackView = $label === 'DIETRO';
        $figure = $this->dressFigureSvg(240, 140, $isBackView);

        File::put($path, <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="1500" viewBox="0 0 900 1500">
  <rect width="900" height="1500" fill="#ffffff"/>
  <text x="450" y="60" text-anchor="middle" font-size="36" font-family="Arial" fill="#4f433c">{$label}</text>
  {$figure}
</svg>
SVG);
    }

    private function createDetailSvg(string $path, string $title, array $lines, string $accentHex): void
    {
        $escapedTitle = $this->escapeSvgText(mb_strtoupper($title));
        $lineMarkup = collect($lines)
            ->values()
            ->map(function (string $line, int $index): string {
                $y = 410 + ($index * 58);

                return '<text x="70" y="' . $y . '" font-size="28" font-family="Arial" fill="#4f433c">- ' . $this->escapeSvgText($line) . '</text>';
            })
            ->implode("\n");

        File::put($path, <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="700" viewBox="0 0 900 700">
  <rect width="900" height="700" fill="#ffffff"/>
  <rect x="20" y="20" width="860" height="660" fill="none" stroke="#d7c7b9" stroke-width="2"/>
  <rect x="40" y="60" width="320" height="240" fill="{$accentHex}" stroke="#8da2b5" stroke-width="2"/>
  <path d="M100 250 C140 150, 260 150, 300 250" fill="none" stroke="#8da2b5" stroke-width="6"/>
  <path d="M150 250 Q200 310 250 250" fill="none" stroke="#8da2b5" stroke-width="6"/>
  <text x="70" y="340" font-size="34" font-family="Arial" fill="#4f433c">{$escapedTitle}</text>
  {$lineMarkup}
</svg>
SVG);
    }

    private function createSwatchSvg(string $path, string $label, string $primaryHex, string $secondaryHex): void
    {
        $escapedLabel = $this->escapeSvgText(mb_strtoupper($label));

        File::put($path, <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="420" viewBox="0 0 600 420">
  <rect width="600" height="420" fill="#ffffff"/>
  <rect x="50" y="40" width="500" height="260" fill="{$primaryHex}" stroke="#d7c7b9" stroke-width="2"/>
  <path d="M40 300 L120 40 M88 300 L168 40 M136 300 L216 40 M184 300 L264 40 M232 300 L312 40 M280 300 L360 40 M328 300 L408 40 M376 300 L456 40 M424 300 L504 40 M472 300 L552 40" stroke="{$secondaryHex}" stroke-width="10" stroke-linecap="round" opacity="0.45"/>
  <path d="M70 90 L530 90 M70 150 L530 150 M70 210 L530 210 M70 270 L530 270" stroke="{$secondaryHex}" stroke-width="6" opacity="0.35"/>
  <text x="300" y="360" text-anchor="middle" font-size="28" font-family="Arial" fill="#4f433c">{$escapedLabel}</text>
</svg>
SVG);
    }

    private function dressFigureSvg(int $x, int $y, bool $backView = false): string
    {
        $center = $x + 135;
        $torsoTopLeft = ($x + 60) . ',' . ($y + 150);
        $torsoTopRight = ($x + 210) . ',' . ($y + 150);
        $torsoRightWaist = ($x + 240) . ',' . ($y + 480);
        $torsoRightBottom = ($x + 225) . ',' . ($y + 1140);
        $torsoLeftBottom = ($x + 45) . ',' . ($y + 1140);
        $torsoLeftWaist = ($x + 30) . ',' . ($y + 480);
        $leftSleeveCx = $x + 40;
        $rightSleeveCx = $x + 230;
        $sleeveCy = $y + 345;
        $leftCuffX = $x + 10;
        $rightCuffX = $x + 200;
        $cuffY = $y + 420;
        $leftLegX1 = $x + 90;
        $leftLegX2 = $x + 80;
        $rightLegX1 = $x + 180;
        $rightLegX2 = $x + 190;
        $legY1 = $y + 1138;
        $legY2 = $y + 1320;
        $leftShoeX = $x + 65;
        $rightShoeX = $x + 165;
        $shoeY = $y + 1320;

        $frontLines = $backView
            ? '<line x1="' . $center . '" y1="' . ($y + 160) . '" x2="' . $center . '" y2="' . ($y + 1138) . '" stroke="#9bb7cf" stroke-width="4"/>'
            : '<path d="M' . ($x + 85) . ' ' . ($y + 170) . ' C' . ($x + 100) . ' ' . ($y + 240) . ', ' . ($x + 120) . ' ' . ($y + 300) . ', ' . $center . ' ' . ($y + 320) . '" fill="none" stroke="#9bb7cf" stroke-width="4"/>
               <path d="M' . ($x + 185) . ' ' . ($y + 170) . ' C' . ($x + 170) . ' ' . ($y + 240) . ', ' . ($x + 150) . ' ' . ($y + 300) . ', ' . $center . ' ' . ($y + 320) . '" fill="none" stroke="#9bb7cf" stroke-width="4"/>';

        return <<<SVG
<g>
  <ellipse cx="{$center}" cy="{$y}" rx="42" ry="48" fill="#cbd8e5"/>
  <ellipse cx="{$center}" cy="{$y}" rx="38" ry="44" fill="#f2e9df"/>
  <polygon points="{$torsoTopLeft} {$torsoTopRight} {$torsoRightWaist} {$torsoRightBottom} {$torsoLeftBottom} {$torsoLeftWaist}" fill="#d9e8f5" stroke="#9bb7cf" stroke-width="4"/>
  <ellipse cx="{$leftSleeveCx}" cy="{$sleeveCy}" rx="50" ry="105" fill="#eff4fb" stroke="#9bb7cf" stroke-width="4"/>
  <ellipse cx="{$rightSleeveCx}" cy="{$sleeveCy}" rx="50" ry="105" fill="#eff4fb" stroke="#9bb7cf" stroke-width="4"/>
  <rect x="{$leftCuffX}" y="{$cuffY}" width="60" height="280" fill="#d9e8f5" stroke="#9bb7cf" stroke-width="4"/>
  <rect x="{$rightCuffX}" y="{$cuffY}" width="60" height="280" fill="#d9e8f5" stroke="#9bb7cf" stroke-width="4"/>
  {$frontLines}
  <line x1="{$leftLegX1}" y1="{$legY1}" x2="{$leftLegX2}" y2="{$legY2}" stroke="#9bb7cf" stroke-width="4"/>
  <line x1="{$rightLegX1}" y1="{$legY1}" x2="{$rightLegX2}" y2="{$legY2}" stroke="#9bb7cf" stroke-width="4"/>
  <rect x="{$leftShoeX}" y="{$shoeY}" width="40" height="40" fill="#d7c7b9"/>
  <rect x="{$rightShoeX}" y="{$shoeY}" width="40" height="40" fill="#d7c7b9"/>
</g>
SVG;
    }

    private function escapeSvgText(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function relativeAssetPath(string $fileName): string
    {
        return self::ASSET_DIRECTORY . '/' . $fileName;
    }

    private function absoluteAssetPath(string $relativePath): string
    {
        return storage_path('app/public/' . $relativePath);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function onlyExistingColumns(string $table, array $attributes): array
    {
        static $columns = [];

        if (! Schema::hasTable($table)) {
            return [];
        }

        if (! array_key_exists($table, $columns)) {
            $columns[$table] = array_flip(Schema::getColumnListing($table));
        }

        return array_intersect_key($attributes, $columns[$table]);
    }
}
