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
        Schema::table('trips_users', function (Blueprint $table) {
            $table->enum('transport_type', ['car', 'plane', 'bus', 'train', 'other'])
                  ->nullable()
                  ->comment('Tipo de transporte do participante');

            $table->dateTime('transport_datetime')
                  ->nullable()
                  ->comment('Data e hora do transporte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips_users', function (Blueprint $table) {
            $table->dropColumn(['transport_type', 'transport_datetime']);
        });
    }
};
