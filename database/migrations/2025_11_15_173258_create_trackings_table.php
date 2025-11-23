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
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('name');
            $table->string('responsible');
            $table->dateTime('approval_date')->nullable();
            $table->integer('status')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackings');
    }
};
