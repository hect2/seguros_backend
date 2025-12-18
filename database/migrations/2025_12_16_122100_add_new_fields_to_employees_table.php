<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('digessp_code')->nullable();
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->string('employee_code')->unique();
            $table->date('admission_date');
            $table->date('departure_date')->nullable();
            $table->foreignId('client_id')->constrained('business');
            $table->string('turn')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->date('suspension_date')->nullable();
            $table->string('life_insurance_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'digessp_code',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'employee_code',
                'admission_date',
                'departure_date',
                'client_id',
                'position_id',
                'employee_status_id',
                'turn',
                'reason_for_leaving',
                'suspension_date',
                'life_insurance_code',
            ]);
        });
    }
};
