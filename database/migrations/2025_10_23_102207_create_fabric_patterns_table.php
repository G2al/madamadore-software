<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fabric_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fabric_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nome fantasia
            $table->string('image')->nullable(); // Foto della fantasia
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fabric_patterns');
    }
};