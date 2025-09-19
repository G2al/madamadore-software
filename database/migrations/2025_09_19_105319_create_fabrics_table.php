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
Schema::create('fabrics', function (Blueprint $table) {
    $table->id();
    $table->string('name');                // Nome tessuto
    $table->string('type')->nullable();    // Tipologia (es. seta, cotone)
    $table->string('color_code')->nullable(); // Codice colore
    $table->string('supplier')->nullable();   // Fornitore
    $table->decimal('purchase_price', 8, 2)->default(0); // Prezzo acquisto unitario
    $table->decimal('client_price', 8, 2)->nullable();   // Prezzo cliente unitario
    $table->string('image')->nullable();   // Foto del tessuto
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabrics');
    }
};
