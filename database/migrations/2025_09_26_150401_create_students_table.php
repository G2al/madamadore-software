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
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->string('cognome');
    $table->string('telefono')->nullable();
    $table->decimal('costo_lezione', 8, 2)->default(0); // costo singola lezione
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
