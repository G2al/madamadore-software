<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            // Aggiungiamo i campi mancanti solo se non esistono
            if (!Schema::hasColumn('dresses', 'total_purchase_cost')) {
                $table->decimal('total_purchase_cost', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dresses', 'total_client_price')) {
                $table->decimal('total_client_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dresses', 'total_profit')) {
                $table->decimal('total_profit', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dresses', 'remaining_balance')) {
                $table->decimal('remaining_balance', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dresses', 'manual_client_price')) {
                $table->decimal('manual_client_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('dresses', 'use_manual_price')) {
                $table->boolean('use_manual_price')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->dropColumn([
                'total_purchase_cost',
                'total_client_price',
                'total_profit',
                'remaining_balance',
                'manual_client_price',
                'use_manual_price',
            ]);
        });
    }
};
