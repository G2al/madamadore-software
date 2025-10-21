<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->decimal('quantity', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->decimal('price', 10, 2)->default(0)->nullable(false)->change();
            $table->decimal('quantity', 10, 2)->default(1)->nullable(false)->change();
        });
    }
};