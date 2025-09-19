<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::table('dress_fabrics', function (Blueprint $table) {
        $table->foreignId('fabric_id')
              ->nullable()
              ->constrained('fabrics')
              ->nullOnDelete(); // se cancello un Fabric, il campo diventa NULL
    });
}

public function down(): void
{
    Schema::table('dress_fabrics', function (Blueprint $table) {
        $table->dropConstrainedForeignId('fabric_id');
    });
}

};
