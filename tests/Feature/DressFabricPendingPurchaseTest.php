<?php

namespace Tests\Feature;

use App\Models\DressFabric;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DressFabricPendingPurchaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('dress_fabrics');
        Schema::dropIfExists('dresses');

        Schema::create('dresses', function (Blueprint $table): void {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('status');
            $table->date('delivery_date')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dress_fabrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dress_id');
            $table->string('name')->nullable();
            $table->decimal('meters', 8, 2)->nullable();
            $table->decimal('purchase_price', 8, 2)->nullable();
            $table->decimal('client_price', 8, 2)->nullable();
            $table->string('color_code')->nullable();
            $table->string('supplier')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('dress_fabrics');
        Schema::dropIfExists('dresses');

        parent::tearDown();
    }

    public function test_pending_purchase_scope_returns_only_confirmed_dress_fabrics(): void
    {
        $confirmedDressId = DB::table('dresses')->insertGetId([
            'customer_name' => 'Cliente Confermato',
            'status' => 'confermato',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cuttingDressId = DB::table('dresses')->insertGetId([
            'customer_name' => 'Cliente Da Tagliare',
            'status' => 'da_tagliare',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $confirmedFabricId = DB::table('dress_fabrics')->insertGetId([
            'dress_id' => $confirmedDressId,
            'name' => 'Mikado',
            'meters' => 4,
            'purchase_price' => 20,
            'client_price' => 35,
            'color_code' => 'BI01',
            'supplier' => 'Fornitore A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('dress_fabrics')->insert([
            'dress_id' => $cuttingDressId,
            'name' => 'Chiffon',
            'meters' => 3,
            'purchase_price' => 12,
            'client_price' => 25,
            'color_code' => 'RO02',
            'supplier' => 'Fornitore B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ids = DressFabric::query()
            ->pendingPurchase()
            ->pluck('dress_fabrics.id')
            ->all();

        $this->assertSame([$confirmedFabricId], $ids);
    }
}
