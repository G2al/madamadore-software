<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dresses
        Schema::table('dresses', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->date('ceremony_date')->nullable()->change();
            $table->enum('ceremony_type', [
                'matrimonio',
                'battesimo', 
                'comunione',
                'cresima',
                'festa_18anni',
                'laurea',
                'altro'
            ])->nullable()->change();
            $table->string('ceremony_holder')->nullable()->change();
            $table->date('delivery_date')->nullable()->change();
            $table->string('estimated_time')->nullable()->change();
        });

        // Dress fabrics
        Schema::table('dress_fabrics', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('type')->nullable()->change();
            $table->decimal('meters', 8, 2)->nullable()->change();
            $table->decimal('purchase_price', 8, 2)->nullable()->change();
            $table->decimal('client_price', 8, 2)->nullable()->change();
            $table->string('color_code')->nullable()->change();
            $table->string('supplier')->nullable()->change();
        });

        // Dress extras
        Schema::table('dress_extras', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->decimal('cost', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Torna indietro mettendo NOT NULL
        Schema::table('dresses', function (Blueprint $table) {
            $table->string('customer_name')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
            $table->date('ceremony_date')->nullable(false)->change();
            $table->enum('ceremony_type', [
                'matrimonio',
                'battesimo', 
                'comunione',
                'cresima',
                'festa_18anni',
                'laurea',
                'altro'
            ])->nullable(false)->change();
            $table->string('ceremony_holder')->nullable(false)->change();
            $table->date('delivery_date')->nullable(false)->change();
            $table->string('estimated_time')->nullable(false)->change();
        });

        Schema::table('dress_fabrics', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('type')->nullable(false)->change();
            $table->decimal('meters', 8, 2)->nullable(false)->change();
            $table->decimal('purchase_price', 8, 2)->nullable(false)->change();
            $table->decimal('client_price', 8, 2)->nullable(false)->change();
            $table->string('color_code')->nullable(false)->change();
            $table->string('supplier')->nullable(false)->change();
        });

        Schema::table('dress_extras', function (Blueprint $table) {
            $table->string('description')->nullable(false)->change();
            $table->decimal('cost', 8, 2)->nullable(false)->change();
        });
    }
};
