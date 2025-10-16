<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->enum('unit_type', ['pezzi', 'metri'])->default('pezzi');
            $table->string('supplier')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_items');
    }
};
