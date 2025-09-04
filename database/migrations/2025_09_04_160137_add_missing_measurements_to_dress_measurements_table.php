<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dress_measurements', function (Blueprint $table) {
            // Elenco NUOVI campi (coerenti con la scheda da 34 voci)
            $toAdd = [
                'seno',
                'bacino',
                'lunghezza_bacino',
                'lunghezza_seno',
                'precisapince',
                'scollo',
                'scollo_dietro',
                'lunghezza_vita',
                'lunghezza_vita_dietro',
                'inclinazione_spalle',
                'larghezza_torace_interno',
                'lunghezza_taglio',
                'lunghezza_gonna_avanti',
                'lunghezza_gonna_dietro',
                'lunghezza_gomito',
                'livello_ascellare',
                'lunghezza_pantalone_interno',
                'lunghezza_pantalone_esterno',
                'lunghezza_ginocchio',
                'circonferenza_ginocchio',
                'circonferenza_taglio',
            ];

            foreach ($toAdd as $col) {
                if (! Schema::hasColumn('dress_measurements', $col)) {
                    $table->decimal($col, 5, 1)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('dress_measurements', function (Blueprint $table) {
            $table->dropColumn([
                'seno',
                'bacino',
                'lunghezza_bacino',
                'lunghezza_seno',
                'precisapince',
                'scollo',
                'scollo_dietro',
                'lunghezza_vita',
                'lunghezza_vita_dietro',
                'inclinazione_spalle',
                'larghezza_torace_interno',
                'lunghezza_taglio',
                'lunghezza_gonna_avanti',
                'lunghezza_gonna_dietro',
                'lunghezza_gomito',
                'livello_ascellare',
                'lunghezza_pantalone_interno',
                'lunghezza_pantalone_esterno',
                'lunghezza_ginocchio',
                'circonferenza_ginocchio',
                'circonferenza_taglio',
            ]);
        });
    }
};
