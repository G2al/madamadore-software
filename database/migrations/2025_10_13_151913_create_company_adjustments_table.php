<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('client_price', 8, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('remaining', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->date('delivery_date')->nullable();
            $table->timestamps();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('referente')->nullable();
            $table->enum('status', ['confermato', 'in_lavorazione', 'consegnato'])->default('confermato');
            $table->boolean('ritirato')->default(false);
            $table->boolean('saldato')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_adjustments');
    }
};