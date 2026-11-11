<?php

namespace Tests\Feature;

use App\Filament\Resources\DressResource\Pages\EditDress;
use App\Models\Dress;
use App\Models\DressExpense;
use App\Models\ShoppingItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseFormSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dress_form_separates_samples_from_purchase_items(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $dress = Dress::query()->create([
            'customer_name' => 'Cliente Form',
            'phone_number' => '3331231231',
            'status' => 'confermato',
        ]);

        Livewire::test(EditDress::class, ['record' => $dress->getRouteKey()])
            ->assertFormFieldExists('availableExpenses')
            ->assertFormFieldExists('purchaseExpenses')
            ->fillForm([
                'availableExpenses' => [[
                    'name' => 'Foto campione bottone',
                    'price' => 0,
                ]],
                'purchaseExpenses' => [[
                    'name' => 'Gross grain',
                    'quantity' => 2,
                    'unit_type' => 'metri',
                    'price' => 0,
                ]],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $sample = DressExpense::query()->where('name', 'Foto campione bottone')->firstOrFail();
        $purchase = DressExpense::query()->where('name', 'Gross grain')->firstOrFail();

        $this->assertFalse($sample->is_to_purchase);
        $this->assertTrue($purchase->is_to_purchase);
        $this->assertDatabaseMissing('shopping_items', [
            'source_type' => DressExpense::class,
            'source_id' => $sample->id,
        ]);
        $this->assertDatabaseHas('shopping_items', [
            'source_type' => DressExpense::class,
            'source_id' => $purchase->id,
            'name' => 'Gross grain',
        ]);
        $this->assertSame(1, ShoppingItem::query()->count());
    }
}
