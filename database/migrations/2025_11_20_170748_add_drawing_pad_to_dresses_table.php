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
        if (Schema::hasColumn('dresses', 'drawing_pad')) {
            return;
        }

        Schema::table('dresses', function (Blueprint $table) {
            $table->longText('drawing_pad')->nullable()->after('final_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('dresses', 'drawing_pad')) {
            return;
        }

        Schema::table('dresses', function (Blueprint $table) {
            $table->dropColumn('drawing_pad');
        });
    }
};
