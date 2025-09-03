<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dress_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dress_id')->constrained()->cascadeOnDelete();
            
            // 20 misure fisse
            $table->decimal('spalle', 5, 1)->nullable();
            $table->decimal('torace', 5, 1)->nullable();
            $table->decimal('sotto_seno', 5, 1)->nullable();
            $table->decimal('vita', 5, 1)->nullable();
            $table->decimal('fianchi', 5, 1)->nullable();
            $table->decimal('lunghezza_busto', 5, 1)->nullable();
            $table->decimal('lunghezza_manica', 5, 1)->nullable();
            $table->decimal('circonferenza_braccio', 5, 1)->nullable();
            $table->decimal('circonferenza_polso', 5, 1)->nullable();
            $table->decimal('altezza_totale', 5, 1)->nullable();
            $table->decimal('lunghezza_abito', 5, 1)->nullable();
            $table->decimal('lunghezza_gonna', 5, 1)->nullable();
            $table->decimal('circonferenza_collo', 5, 1)->nullable();
            $table->decimal('larghezza_schiena', 5, 1)->nullable();
            $table->decimal('altezza_seno', 5, 1)->nullable();
            $table->decimal('distanza_seni', 5, 1)->nullable();
            $table->decimal('circonferenza_coscia', 5, 1)->nullable();
            $table->decimal('lunghezza_cavallo', 5, 1)->nullable();
            $table->decimal('altezza_ginocchio', 5, 1)->nullable();
            $table->decimal('circonferenza_caviglia', 5, 1)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dress_measurements');
    }
};