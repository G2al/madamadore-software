<?php

namespace Tests\Feature;

use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\CompanyAdjustment;
use App\Models\CompanyAdjustmentItem;
use App\Models\Worker;
use App\Services\WorkerProductionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkerProductionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('company_adjustment_items');
        Schema::dropIfExists('adjustment_items');
        Schema::dropIfExists('company_adjustments');
        Schema::dropIfExists('adjustments');
        Schema::dropIfExists('workers');

        Schema::create('workers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('adjustments', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->default('in_lavorazione');
            $table->foreignId('primary_worker_id')->nullable();
            $table->timestamps();
        });

        Schema::create('company_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->default('in_lavorazione');
            $table->foreignId('primary_worker_id')->nullable();
            $table->timestamps();
        });

        Schema::create('adjustment_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('adjustment_id');
            $table->foreignId('worker_id')->nullable();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('company_adjustment_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_adjustment_id');
            $table->foreignId('worker_id')->nullable();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('company_adjustment_items');
        Schema::dropIfExists('adjustment_items');
        Schema::dropIfExists('company_adjustments');
        Schema::dropIfExists('adjustments');
        Schema::dropIfExists('workers');

        parent::tearDown();
    }

    public function test_worker_daily_production_counts_completed_normal_and_company_items(): void
    {
        $worker = Worker::create(['name' => 'Anna']);

        $adjustment = Adjustment::create(['status' => 'in_lavorazione']);
        $normalItem = AdjustmentItem::create([
            'adjustment_id' => $adjustment->id,
            'worker_id' => $worker->id,
            'name' => 'Orlo pantalone',
            'price' => 10,
        ]);

        $companyAdjustment = CompanyAdjustment::create(['status' => 'confermato']);
        CompanyAdjustmentItem::create([
            'company_adjustment_id' => $companyAdjustment->id,
            'worker_id' => $worker->id,
            'name' => 'Cambio zip',
            'price' => 15,
        ]);

        $adjustment->update(['status' => 'confermato']);

        $this->assertNotNull($normalItem->refresh()->completed_at);

        $totals = app(WorkerProductionService::class)->totalsForDate($worker, now());

        $this->assertSame(2, $totals['work_count']);
        $this->assertSame(25.0, $totals['total_amount']);
    }
}
