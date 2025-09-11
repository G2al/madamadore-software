<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->decimal('manufacturing_price', 8, 2)->default(0)->after('estimated_time');
        });
    }

    public function down()
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->dropColumn('manufacturing_price');
        });
    }
};