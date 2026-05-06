<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('dress_technical_sheets', 'technical_drawing_image')) {
            Schema::table('dress_technical_sheets', function (Blueprint $table) {
                $table->string('technical_drawing_image')->nullable()->after('sleeve_fabric_color');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dress_technical_sheets', 'technical_drawing_image')) {
            Schema::table('dress_technical_sheets', function (Blueprint $table) {
                $table->dropColumn('technical_drawing_image');
            });
        }
    }
};
