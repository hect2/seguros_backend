<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id');
            $table->string('name');               // Nombre del puesto
            $table->string('location')->nullable(); // Dirección / ubicación
            $table->string('shift')->nullable();    // Día, noche, 24x24, etc
            $table->string('service_type')->nullable(); // Armado, custodio, monitoreo
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_positions');
    }
};
