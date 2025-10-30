<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('special_dresses', function (Blueprint $table) {
            $table->id();

            // Dati cliente
            $table->string('customer_name')->nullable();
            $table->string('phone_number')->nullable();

            // Festività / tipo cerimonia (stesso campo dei Dress per coerenza)
            $table->string('ceremony_type')->nullable();

            // Date & immagini
            $table->date('delivery_date')->nullable();
            $table->string('sketch_image')->nullable();
            $table->string('final_image')->nullable();

            // Note
            $table->text('notes')->nullable();

            // Economica minimale: prezzo + acconto + rimanente
            $table->decimal('total_client_price', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->decimal('remaining', 10, 2)->default(0);

            // Stato pipeline (riuso config('dress.statuses'))
            $table->string('status')->default('in_attesa_acconto');

            // Campi consegna finale (come Dress Consegnati)
            $table->boolean('ritirato')->default(false);
            $table->boolean('saldato')->default(false);
            $table->string('payment_method')->nullable();

            // Archiviazione soft custom come per Dress
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_dresses');
    }
};
