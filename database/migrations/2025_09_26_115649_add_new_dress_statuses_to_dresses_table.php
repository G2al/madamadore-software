<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            // Aggiunge i nuovi stati mantenendo quelli esistenti
            $table->enum('status', [
                'in_attesa_acconto',
                'confermato',
                'in_lavorazione',      // <- MANTENIAMO questo
                'da_tagliare',         // <- NUOVO
                'pronta_misura',       // <- NUOVO
                'consegnato'
            ])->default('in_attesa_acconto')->change();
        });
    }

    public function down(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            // Ripristina l'enum originale (rimuove solo i nuovi stati)
            $table->enum('status', [
                'in_attesa_acconto',
                'confermato',
                'in_lavorazione',
                'consegnato'
            ])->default('in_attesa_acconto')->change();
        });
    }
};