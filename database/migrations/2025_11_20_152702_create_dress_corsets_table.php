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
        Schema::create('dress_corsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dress_id')->constrained()->cascadeOnDelete();

            // Pinza Vita (davanti, lato, dietro)
            $table->decimal('pinza_vita_davanti', 5, 1)->nullable();
            $table->decimal('pinza_vita_lato', 5, 1)->nullable();
            $table->decimal('pinza_vita_dietro', 5, 1)->nullable();

            // Pinza Fianchi (davanti, lato, dietro)
            $table->decimal('pinza_fianchi_davanti', 5, 1)->nullable();
            $table->decimal('pinza_fianchi_lato', 5, 1)->nullable();
            $table->decimal('pinza_fianchi_dietro', 5, 1)->nullable();

            // Linea sotto seno (davanti, lato, dietro)
            $table->decimal('linea_sottoseno_davanti', 5, 1)->nullable();
            $table->decimal('linea_sottoseno_lato', 5, 1)->nullable();
            $table->decimal('linea_sottoseno_dietro', 5, 1)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dress_corsets');
    }
};
