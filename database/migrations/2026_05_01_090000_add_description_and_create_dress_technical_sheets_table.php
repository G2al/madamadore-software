<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('dresses', 'description')) {
            Schema::table('dresses', function (Blueprint $table) {
                $table->text('description')->nullable()->after('drawing_image');
            });
        }

        if (! Schema::hasTable('dress_technical_sheets')) {
            Schema::create('dress_technical_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dress_id')->constrained()->cascadeOnDelete()->unique();
                $table->string('model_name')->nullable();
                $table->string('line_name')->nullable();
                $table->string('garment_type')->nullable();
                $table->text('client_notes')->nullable();
                $table->text('technical_description')->nullable();
                $table->text('production_notes')->nullable();
                $table->text('construction_notes')->nullable();
                $table->text('materials_notes')->nullable();
                $table->text('accessories_notes')->nullable();
                $table->string('measurements_responsible')->nullable();
                $table->text('nb_notes')->nullable();
                $table->text('neckline_details')->nullable();
                $table->text('sleeve_details')->nullable();
                $table->text('bodice_details')->nullable();
                $table->text('back_details')->nullable();
                $table->text('closure_details')->nullable();
                $table->string('main_fabric_name')->nullable();
                $table->string('main_fabric_composition')->nullable();
                $table->string('main_fabric_color')->nullable();
                $table->string('sleeve_fabric_name')->nullable();
                $table->string('sleeve_fabric_composition')->nullable();
                $table->string('sleeve_fabric_color')->nullable();
                $table->string('front_view_image')->nullable();
                $table->string('back_view_image')->nullable();
                $table->string('neckline_detail_image')->nullable();
                $table->string('sleeve_detail_image')->nullable();
                $table->string('bodice_detail_image')->nullable();
                $table->string('back_detail_image')->nullable();
                $table->string('closure_detail_image')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dress_technical_sheets')) {
            Schema::drop('dress_technical_sheets');
        }

        if (Schema::hasColumn('dresses', 'description')) {
            Schema::table('dresses', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
