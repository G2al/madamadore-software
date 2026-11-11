<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, class-string> */
    private array $sources = [
        'dress_expenses' => App\Models\DressExpense::class,
        'adjustment_expenses' => App\Models\AdjustmentExpense::class,
        'company_adjustment_expenses' => App\Models\CompanyAdjustmentExpense::class,
    ];

    public function up(): void
    {
        Schema::table('company_adjustment_expenses', function (Blueprint $table): void {
            $table->string('name')->nullable()->change();
        });

        foreach (array_keys($this->sources) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->boolean('is_to_purchase')->default(false)->after('supplier_id');
            });
        }

        foreach ($this->sources as $tableName => $sourceType) {
            DB::table($tableName)
                ->whereNotNull('name')
                ->whereRaw("TRIM(name) <> ''")
                ->whereRaw('LOWER(TRIM(name)) <> ?', ['articolo generico'])
                ->whereExists(function ($query) use ($tableName, $sourceType): void {
                    $query->selectRaw('1')
                        ->from('shopping_items')
                        ->whereColumn('shopping_items.source_id', "{$tableName}.id")
                        ->where('shopping_items.source_type', $sourceType);
                })
                ->update(['is_to_purchase' => true]);

            DB::table($tableName)
                ->whereRaw('LOWER(TRIM(name)) = ?', ['articolo generico'])
                ->update(['name' => null]);

            DB::table('shopping_items')
                ->where('source_type', $sourceType)
                ->whereIn('source_id', DB::table($tableName)
                    ->select('id')
                    ->where('is_to_purchase', false))
                ->delete();
        }
    }

    public function down(): void
    {
        DB::table('company_adjustment_expenses')
            ->whereNull('name')
            ->update(['name' => 'Articolo generico']);

        foreach (array_keys($this->sources) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('is_to_purchase');
            });
        }

        Schema::table('company_adjustment_expenses', function (Blueprint $table): void {
            $table->string('name')->nullable(false)->change();
        });
    }
};
