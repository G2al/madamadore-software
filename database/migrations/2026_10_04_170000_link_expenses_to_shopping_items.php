<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['dress_expenses', 'adjustment_expenses', 'company_adjustment_expenses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('color_code')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->enum('unit_type', ['pezzi', 'metri'])->default('pezzi');
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            });
        }

        Schema::table('shopping_items', function (Blueprint $table): void {
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unique(['source_type', 'source_id'], 'shopping_items_source_unique');
        });

        $this->backfillExpenses('dress_expenses', App\Models\DressExpense::class);
        $this->backfillExpenses('adjustment_expenses', App\Models\AdjustmentExpense::class);
        $this->backfillExpenses('company_adjustment_expenses', App\Models\CompanyAdjustmentExpense::class);
    }

    public function down(): void
    {
        DB::table('shopping_items')
            ->whereIn('source_type', [
                App\Models\DressExpense::class,
                App\Models\AdjustmentExpense::class,
                App\Models\CompanyAdjustmentExpense::class,
            ])
            ->delete();

        Schema::table('shopping_items', function (Blueprint $table): void {
            $table->dropUnique('shopping_items_source_unique');
            $table->dropColumn(['source_type', 'source_id']);
        });

        foreach (['dress_expenses', 'adjustment_expenses', 'company_adjustment_expenses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('supplier_id');
                $table->dropColumn(['color_code', 'quantity', 'unit_type']);
            });
        }
    }

    private function backfillExpenses(string $tableName, string $sourceType): void
    {
        DB::table($tableName)
            ->orderBy('id')
            ->chunkById(200, function ($expenses) use ($sourceType): void {
                foreach ($expenses as $expense) {
                    DB::table('shopping_items')->insert([
                        'name' => $expense->name ?: 'Articolo generico',
                        'color_code' => null,
                        'price' => $expense->price ?? 0,
                        'quantity' => 1,
                        'unit_type' => 'pezzi',
                        'supplier' => null,
                        'supplier_id' => null,
                        'photo_path' => $expense->photo_path,
                        'purchase_date' => null,
                        'source_type' => $sourceType,
                        'source_id' => $expense->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }
};
