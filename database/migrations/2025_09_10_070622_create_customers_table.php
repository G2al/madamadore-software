<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome cliente
            $table->string('phone_number')->nullable(); // Numero di telefono (può anche mancare)
            $table->timestamps();

            // opzionale: per evitare duplicati sul numero
            $table->unique('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
