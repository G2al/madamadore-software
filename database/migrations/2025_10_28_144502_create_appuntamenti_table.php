<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appuntamenti', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('cognome', 100);
            $table->string('telefono', 20);
            $table->date('data_appuntamento');
            $table->time('ora_appuntamento');
            $table->text('descrizione')->nullable();
            $table->enum('stato', ['da_fare', 'fatto', 'scaduto'])->default('da_fare');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appuntamenti');
    }
};
