<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // referencia original
            $table->json('data'); // snapshot completo en JSON
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_backups');
    }
};
