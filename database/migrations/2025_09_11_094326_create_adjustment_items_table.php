<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nome dell'aggiusto (es. "Pantalone nero")
            $table->text('description')->nullable(); // Descrizione di cosa è stato fatto
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adjustment_items');
    }
};