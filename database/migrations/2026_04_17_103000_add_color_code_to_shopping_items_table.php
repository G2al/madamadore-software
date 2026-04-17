<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shopping_items', 'color_code')) {
            return;
        }

        Schema::table('shopping_items', function (Blueprint $table): void {
            $table->string('color_code')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('shopping_items', 'color_code')) {
            return;
        }

        Schema::table('shopping_items', function (Blueprint $table): void {
            $table->dropColumn('color_code');
        });
    }
};
