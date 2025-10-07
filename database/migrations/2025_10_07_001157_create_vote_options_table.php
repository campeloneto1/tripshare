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
        Schema::create('votes_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_question_id')->constrained('votes_questions')->onDelete('cascade');

            $table->string('title'); // Nome da opção
            $table->json('json_data')->nullable(); // Informações do local/evento (opcional)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes_options');
    }
};
