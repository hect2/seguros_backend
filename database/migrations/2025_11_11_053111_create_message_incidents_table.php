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
        Schema::create('message_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_message_reply')->nullable()
                ->constrained('message_incidents')
                ->cascadeOnDelete();
            $table->foreignId('id_incident')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->text('message');

            // JSON opcional para adjuntos (igual que en Incident)
            $table->json('attachments')->nullable()
                ->comment('Estructura: [{"name":"log.txt","path":"/storage/messages/12/log.txt","uploaded_at":"2025-11-08T11:05:00Z"}]');

            $table->timestamps();

            $table->index(['id_incident', 'id_user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_incidents');
    }
};
