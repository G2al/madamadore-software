<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dress_fabrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dress_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');
            $table->string('type');
            $table->decimal('meters', 8, 2);
            $table->decimal('purchase_price', 8, 2);
            $table->decimal('client_price', 8, 2);
            $table->string('color_code');
            $table->string('supplier');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dress_fabrics');
    }
};