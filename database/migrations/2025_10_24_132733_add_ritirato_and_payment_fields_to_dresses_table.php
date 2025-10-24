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
        Schema::table('dresses', function (Blueprint $table) {
            $table->boolean('ritirato')->default(false)->after('status');
            $table->boolean('saldato')->default(false)->after('ritirato');
            $table->string('payment_method')->nullable()->after('saldato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->dropColumn(['ritirato', 'saldato', 'payment_method']);
        });
    }
};