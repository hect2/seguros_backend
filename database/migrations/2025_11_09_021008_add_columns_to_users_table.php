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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('status')->default(1)->after('name');
            $table->string('dpi', 20)->after('email');
            $table->string('phone', 20)->nullable()->after('dpi');
            $table->json('district')->nullable()->after('phone');
            $table->text('observations')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'dpi',
                'phone',
                'district',
                'observations',
            ]);
        });
    }
};
