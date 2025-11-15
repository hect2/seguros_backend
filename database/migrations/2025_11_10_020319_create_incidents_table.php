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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->foreignId('type_id')->constrained('types');
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('criticity_id')->constrained('criticals');
            $table->text('description')->nullable();

            // JSON para almacenar varios archivos relacionados al incidente
            $table->json('files')->nullable()
                ->comment('Estructura: [{"name":"manual.pdf","path":"/storage/incidents/1/manual.pdf","uploaded_at":"2025-11-08T10:30:00Z"}]');

            $table->foreignId('status_id')->constrained('incident_statuses');

            $table->foreignId('user_reported')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_assigned')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['office_id', 'user_reported', 'user_assigned']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
