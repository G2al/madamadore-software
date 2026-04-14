<?php

namespace Tests\Feature;

use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\CompanyAdjustment;
use App\Models\CompanyAdjustmentItem;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AdjustmentReceiptViewTest extends TestCase
{
    public function test_normal_adjustment_receipt_shows_each_item_price(): void
    {
        $adjustment = $this->makeAdjustment();
        $adjustment->setRelation('items', collect([
            new AdjustmentItem([
                'name' => 'Orlo pantalone',
                'description' => 'Accorciare di 3 cm',
                'price' => 18.50,
            ]),
            new AdjustmentItem([
                'name' => 'Stringere vita',
                'description' => null,
                'price' => 0,
            ]),
        ]));

        $html = view('pdf.adjustment-receipt-thermal', [
            'adjustment' => $adjustment,
        ])->render();

        $this->assertStringContainsString('Prezzo', $html);
        $this->assertStringContainsString('18,50', $html);
        $this->assertStringContainsString('0,00', $html);
    }

    public function test_company_adjustment_receipt_shows_each_item_price(): void
    {
        $adjustment = $this->makeCompanyAdjustment();
        $adjustment->setRelation('items', collect([
            new CompanyAdjustmentItem([
                'name' => 'Cambio zip',
                'description' => 'Sostituzione completa',
                'price' => 25,
            ]),
            new CompanyAdjustmentItem([
                'name' => 'Rinforzo cucitura',
                'description' => 'Punto interno',
                'price' => 12.30,
            ]),
        ]));

        $html = view('pdf.company-adjustment-receipt', [
            'adjustment' => $adjustment,
        ])->render();

        $this->assertStringContainsString('Prezzo', $html);
        $this->assertStringContainsString('25,00', $html);
        $this->assertStringContainsString('12,30', $html);
    }

    private function makeAdjustment(): Adjustment
    {
        $adjustment = new Adjustment([
            'client_price' => 30,
            'deposit' => 10,
            'total' => 30,
            'remaining' => 20,
        ]);

        $adjustment->id = 101;
        $adjustment->created_at = Carbon::parse('2026-04-14');
        $adjustment->setRelation('customer', $this->makeCustomer());
        $adjustment->setRelation('items', new Collection());

        return $adjustment;
    }

    private function makeCompanyAdjustment(): CompanyAdjustment
    {
        $adjustment = new CompanyAdjustment([
            'client_price' => 37.30,
            'deposit' => 15,
            'total' => 37.30,
            'remaining' => 22.30,
        ]);

        $adjustment->id = 202;
        $adjustment->created_at = Carbon::parse('2026-04-14');
        $adjustment->setRelation('customer', $this->makeCustomer());
        $adjustment->setRelation('items', new Collection());

        return $adjustment;
    }

    private function makeCustomer(): Customer
    {
        return new Customer([
            'name' => 'Giulia Test',
            'phone_number' => '3331234567',
        ]);
    }
}
