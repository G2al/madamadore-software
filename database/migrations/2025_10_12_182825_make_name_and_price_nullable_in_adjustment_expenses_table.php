<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('adjustment_expenses', function (Blueprint $table) {
            // ✅ Rende entrambi i campi opzionali
            $table->string('name')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('adjustment_expenses', function (Blueprint $table) {
            // 🔙 In caso di rollback, tornano obbligatori
            $table->string('name')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });
    }
};
