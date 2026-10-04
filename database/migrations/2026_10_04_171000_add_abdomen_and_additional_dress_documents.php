<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dress_corsets', function (Blueprint $table): void {
            $table->decimal('circonferenza_addome', 5, 1)->nullable();
        });

        Schema::table('dresses', function (Blueprint $table): void {
            $table->string('final_notes_photo')->nullable();
            $table->string('secondary_model_image')->nullable();
            $table->text('secondary_model_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dress_corsets', function (Blueprint $table): void {
            $table->dropColumn('circonferenza_addome');
        });

        Schema::table('dresses', function (Blueprint $table): void {
            $table->dropColumn(['final_notes_photo', 'secondary_model_image', 'secondary_model_notes']);
        });
    }
};
