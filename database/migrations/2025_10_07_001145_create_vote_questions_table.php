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
        Schema::create('votes_questions', function (Blueprint $table) {
            $table->id();

            // Morph para indicar onde a votação se aplica (TripDay ou TripDayCity)
            $table->morphs('votable');

            $table->string('title'); // Ex: "Qual cidade incluir?"
            $table->enum('type', ['city', 'event']); // Tipo da votação
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('is_closed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes_questions');
    }
};
