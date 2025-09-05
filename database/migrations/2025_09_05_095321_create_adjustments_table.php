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
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // adjustment name (nome aggiusto)
            $table->string('customer_name'); // cliente
            $table->string('phone_number')->nullable(); // numero di telefono
            $table->decimal('client_price', 10, 2); // prezzo diretto cliente
            $table->decimal('deposit', 10, 2)->nullable(); // acconto facoltativo
            $table->decimal('total', 10, 2); // totale
            $table->decimal('remaining', 10, 2); // rimanente
            $table->decimal('profit', 10, 2); // guadagno
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
