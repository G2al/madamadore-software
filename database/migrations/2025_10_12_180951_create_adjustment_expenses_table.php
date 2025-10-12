<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjustment_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustment_expenses');
    }
};
