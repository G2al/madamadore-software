<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('special_dress_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_dress_id')->constrained('special_dresses')->cascadeOnDelete();

            // Campi "legacy" (coerenza con DressMeasurement)
            $table->float('spalle')->nullable();
            $table->float('torace')->nullable();
            $table->float('sotto_seno')->nullable();
            $table->float('vita')->nullable();
            $table->float('fianchi')->nullable();
            $table->float('lunghezza_busto')->nullable();
            $table->float('lunghezza_manica')->nullable();
            $table->float('circonferenza_braccio')->nullable();
            $table->float('circonferenza_polso')->nullable();
            $table->float('altezza_totale')->nullable();
            $table->float('lunghezza_abito')->nullable();
            $table->float('lunghezza_gonna')->nullable();
            $table->float('circonferenza_collo')->nullable();
            $table->float('larghezza_schiena')->nullable();
            $table->float('altezza_seno')->nullable();
            $table->float('distanza_seni')->nullable();
            $table->float('circonferenza_coscia')->nullable();
            $table->float('lunghezza_cavallo')->nullable();
            $table->float('altezza_ginocchio')->nullable();
            $table->float('circonferenza_caviglia')->nullable();

            // Nuovi (stessa lista di DressMeasurement)
            $table->float('seno')->nullable();
            $table->float('bacino')->nullable();
            $table->float('lunghezza_bacino')->nullable();
            $table->float('lunghezza_seno')->nullable();
            $table->float('precisapince')->nullable();
            $table->float('scollo')->nullable();
            $table->float('scollo_dietro')->nullable();
            $table->float('lunghezza_vita')->nullable();
            $table->float('lunghezza_vita_dietro')->nullable();
            $table->float('inclinazione_spalle')->nullable();
            $table->float('larghezza_torace_interno')->nullable();
            $table->float('lunghezza_taglio')->nullable();
            $table->float('lunghezza_gonna_avanti')->nullable();
            $table->float('lunghezza_gonna_dietro')->nullable();
            $table->float('lunghezza_gomito')->nullable();
            $table->float('livello_ascellare')->nullable();
            $table->float('lunghezza_pantalone_interno')->nullable();
            $table->float('lunghezza_pantalone_esterno')->nullable();
            $table->float('lunghezza_ginocchio')->nullable();
            $table->float('circonferenza_ginocchio')->nullable();
            $table->float('circonferenza_taglio')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_dress_measurements');
    }
};
