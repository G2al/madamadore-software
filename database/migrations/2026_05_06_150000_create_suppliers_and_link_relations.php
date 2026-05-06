<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('phone_number')->nullable()->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('fabrics', 'supplier_id')) {
            Schema::table('fabrics', function (Blueprint $table) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('supplier')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('dress_fabrics', 'supplier_id')) {
            Schema::table('dress_fabrics', function (Blueprint $table) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('supplier')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('shopping_items', 'supplier_id')) {
            Schema::table('shopping_items', function (Blueprint $table) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('supplier')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            });
        }

        $supplierNames = collect()
            ->merge(
                DB::table('fabrics')
                    ->whereNotNull('supplier')
                    ->pluck('supplier')
                    ->all(),
            )
            ->merge(
                DB::table('dress_fabrics')
                    ->whereNotNull('supplier')
                    ->pluck('supplier')
                    ->all(),
            )
            ->merge(
                DB::table('shopping_items')
                    ->whereNotNull('supplier')
                    ->pluck('supplier')
                    ->all(),
            )
            ->map(fn (mixed $name): string => trim((string) $name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values();

        foreach ($supplierNames as $supplierName) {
            DB::table('suppliers')->insertOrIgnore([
                'name' => $supplierName,
                'phone_number' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $suppliers = DB::table('suppliers')
            ->pluck('id', 'name');

        foreach ($suppliers as $supplierName => $supplierId) {
            DB::table('fabrics')
                ->whereRaw('LOWER(TRIM(supplier)) = ?', [mb_strtolower(trim($supplierName))])
                ->update(['supplier_id' => $supplierId]);

            DB::table('dress_fabrics')
                ->whereRaw('LOWER(TRIM(supplier)) = ?', [mb_strtolower(trim($supplierName))])
                ->update(['supplier_id' => $supplierId]);

            DB::table('shopping_items')
                ->whereRaw('LOWER(TRIM(supplier)) = ?', [mb_strtolower(trim($supplierName))])
                ->update(['supplier_id' => $supplierId]);
        }
    }

    public function down(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::table('dress_fabrics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::table('fabrics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::dropIfExists('suppliers');
    }
};
