<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('adjustments', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->date('delivery_date')->nullable()->change();
            $table->decimal('client_price', 8, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('adjustments', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->date('delivery_date')->nullable(false)->change();
            $table->decimal('client_price', 8, 2)->nullable(false)->change();
        });
    }
};