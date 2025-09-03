<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dress_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dress_id')->constrained()->cascadeOnDelete();
            
            $table->string('description');
            $table->decimal('cost', 8, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dress_extras');
    }
};