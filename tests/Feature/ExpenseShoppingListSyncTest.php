<?php

namespace Tests\Feature;

use App\Models\Adjustment;
use App\Models\AdjustmentExpense;
use App\Models\Customer;
use App\Models\Dress;
use App\Models\DressExpense;
use App\Models\ShoppingItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseShoppingListSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_dress_expense_is_kept_in_sync_with_the_shopping_list(): void
    {
        $supplier = Supplier::query()->create([
            'name' => 'Fornitore Test',
            'phone_number' => '+393331234567',
        ]);

        $dress = Dress::query()->create([
            'customer_name' => 'Cliente Test',
            'phone_number' => '3331234567',
            'status' => 'confermato',
        ]);

        $expense = DressExpense::query()->create([
            'dress_id' => $dress->id,
            'supplier_id' => $supplier->id,
            'name' => 'Bottoni madreperla',
            'color_code' => 'B12',
            'quantity' => 6,
            'unit_type' => 'pezzi',
            'price' => 1.50,
        ]);

        $item = ShoppingItem::query()->where([
            'source_type' => DressExpense::class,
            'source_id' => $expense->id,
        ])->firstOrFail();

        $this->assertSame('Bottoni madreperla', $item->name);
        $this->assertSame($supplier->id, $item->supplier_id);
        $this->assertSame('6.00', $item->quantity);

        $item->update(['purchase_date' => now()]);
        $expense->update(['name' => 'Bottoni rivestiti', 'quantity' => 8]);

        $item->refresh();
        $this->assertSame('Bottoni rivestiti', $item->name);
        $this->assertSame('8.00', $item->quantity);
        $this->assertNotNull($item->purchase_date);

        $expense->delete();

        $this->assertDatabaseMissing('shopping_items', ['id' => $item->id]);
    }

    public function test_deleting_a_dress_removes_its_linked_shopping_items(): void
    {
        $dress = Dress::query()->create([
            'customer_name' => 'Cliente Eliminato',
            'phone_number' => '3331112233',
            'status' => 'confermato',
        ]);

        $expense = DressExpense::query()->create([
            'dress_id' => $dress->id,
            'name' => 'Articolo collegato',
        ]);

        $this->assertDatabaseHas('shopping_items', [
            'source_type' => DressExpense::class,
            'source_id' => $expense->id,
        ]);

        $dress->delete();

        $this->assertDatabaseMissing('shopping_items', [
            'source_type' => DressExpense::class,
            'source_id' => $expense->id,
        ]);
    }

    public function test_adjustment_expense_is_added_to_the_shopping_list(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Cliente Aggiusto',
            'phone_number' => '3337654321',
        ]);

        $adjustment = Adjustment::query()->create([
            'customer_id' => $customer->id,
            'status' => 'in_lavorazione',
            'client_price' => 0,
            'deposit' => 0,
            'total' => 0,
            'remaining' => 0,
            'profit' => 0,
        ]);

        $expense = AdjustmentExpense::query()->create([
            'adjustment_id' => $adjustment->id,
            'name' => 'Cerniera invisibile',
            'quantity' => 1,
            'unit_type' => 'pezzi',
        ]);

        $this->assertDatabaseHas('shopping_items', [
            'source_type' => AdjustmentExpense::class,
            'source_id' => $expense->id,
            'name' => 'Cerniera invisibile',
        ]);
    }
}
