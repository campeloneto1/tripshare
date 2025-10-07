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
        // Adicionar índice em xid na tabela places (já tem unique, mas mantém para queries)
        // Não precisa pois o unique constraint já cria índice
        // Schema::table('places', function (Blueprint $table) {
        //     $table->index('xid');
        // });

        // Após refactor, events_reviews e trips_days_events não têm mais xid
        // Não fazemos nada aqui pois os foreign keys já criam índices
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nada a fazer
    }
};
