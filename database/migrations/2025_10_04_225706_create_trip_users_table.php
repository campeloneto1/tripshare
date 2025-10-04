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
       Schema::create('trips_users', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('trip_id')
                  ->constrained('trips')
                  ->onDelete('cascade');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->enum('role', ['admin','participant'])
                  ->default('participant')
                  ->comment('Função do usuário na viagem');

            $table->timestamps();

            $table->unique(['trip_id', 'user_id']); // não permite duplicar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips_users');
    }
};
