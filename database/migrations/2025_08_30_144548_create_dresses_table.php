<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dresses', function (Blueprint $table) {
            $table->id();
            
            // Dati Contatto
            $table->string('customer_name');
            $table->string('phone_number');
            $table->date('ceremony_date');
            $table->enum('ceremony_type', [
                'matrimonio',
                'battesimo', 
                'comunione',
                'cresima',
                'festa_18anni',
                'laurea',
                'altro'
            ]);
            $table->string('ceremony_holder');
            $table->date('delivery_date');
            $table->string('sketch_image')->nullable();
            $table->string('final_image')->nullable();
            $table->text('notes')->nullable();
            
            // Preventivo
            $table->string('estimated_time'); // es: "15 giorni" o "120 ore"
            
            // Campi finali
            $table->decimal('deposit', 8, 2)->default(0);
            $table->enum('status', [
                'in_attesa_acconto',
                'confermato', 
                'in_lavorazione',
                'consegnato'
            ])->default('in_attesa_acconto');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dresses');
    }
};