<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shopping_items', 'fabric_id')) {
            return;
        }

        Schema::table('shopping_items', function (Blueprint $table): void {
            $table->foreignId('fabric_id')
                ->nullable()
                ->after('id')
                ->constrained('fabrics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('shopping_items', 'fabric_id')) {
            return;
        }

        Schema::table('shopping_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('fabric_id');
        });
    }
};
