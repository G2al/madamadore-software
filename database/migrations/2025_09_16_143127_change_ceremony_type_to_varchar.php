<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->string('ceremony_type', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->enum('ceremony_type', [
                'matrimonio', 'battesimo', 'comunione', 'cresima', 
                'festa_18anni', 'laurea', 'altro'
            ])->nullable()->change();
        });
    }
};