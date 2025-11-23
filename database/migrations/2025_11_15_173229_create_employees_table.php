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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('dpi')->unique();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('files')->nullable();
            $table->foreignId('status_id')->constrained('employee_statuses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
