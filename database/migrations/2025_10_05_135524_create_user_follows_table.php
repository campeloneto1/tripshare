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
        Schema::create('users_follows', function (Blueprint $table) {
            $table->id();

            // Usuário que envia o convite
            $table->foreignId('follower_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Usuário que recebe o convite
            $table->foreignId('following_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Status da relação
            $table->enum('status', ['pending', 'accepted'])->default('pending');

            // Data do aceite
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            // Evita duplicidade
            $table->unique(['follower_id', 'following_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_follows');
    }
};
