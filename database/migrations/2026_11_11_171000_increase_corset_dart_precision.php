<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'ripresa_vita_davanti',
        'ripresa_vita_lato',
        'ripresa_vita_dietro',
        'ripresa_fianchi_davanti',
        'ripresa_fianchi_lato',
        'ripresa_fianchi_dietro',
    ];

    public function up(): void
    {
        Schema::table('dress_corsets', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                $table->decimal($column, 7, 3)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dress_corsets', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                $table->decimal($column, 5, 1)->nullable()->change();
            }
        });
    }
};
