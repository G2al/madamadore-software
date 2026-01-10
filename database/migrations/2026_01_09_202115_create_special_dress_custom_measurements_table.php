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
        Schema::create('special_dress_custom_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_dress_id')->constrained()->cascadeOnDelete();

            // Campi per misure personalizzate
            $table->string('label'); // es: "Circonferenza polpaccio sinistro"
            $table->decimal('value', 5, 1)->nullable(); // valore della misura, può essere null
            $table->string('unit', 10)->default('cm'); // unità di misura
            $table->text('notes')->nullable(); // note aggiuntive

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_dress_custom_measurements');
    }
};
