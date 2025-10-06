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
        Schema::create('trips_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, updated, deleted, day_added, event_added, person_added, etc
            $table->string('model_type')->nullable(); // Trip, Day, Event, TripUser, etc
            $table->unsignedBigInteger('model_id')->nullable(); // ID do registro afetado
            $table->json('changes')->nullable(); // Dados do que foi alterado
            $table->text('description')->nullable(); // Descrição legível da ação
            $table->timestamps();

            $table->index(['trip_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips_histories');
    }
};
