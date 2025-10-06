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
         Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            
            // Morph relationship
            $table->morphs('uploadable'); // cria uploadable_type e uploadable_id

            // Dados principais do arquivo
            $table->string('path'); // caminho dentro do storage
            $table->string('original_name')->nullable(); // nome original do arquivo
            $table->string('type')->default('image'); // image, video, document etc.
            $table->integer('size')->nullable(); // tamanho em bytes

            // Campos auxiliares
            $table->unsignedInteger('order')->default(0); // útil em posts com várias imagens
            $table->boolean('is_main')->default(false); // ex: imagem principal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
