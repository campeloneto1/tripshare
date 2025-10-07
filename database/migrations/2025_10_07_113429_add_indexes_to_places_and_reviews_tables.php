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
        // Adicionar índice em xid na tabela places
        Schema::table('places', function (Blueprint $table) {
            $table->index('xid');
        });

        // Adicionar índice em xid na tabela events_reviews
        Schema::table('events_reviews', function (Blueprint $table) {
            $table->index('xid');
        });

        // Adicionar índice em xid na tabela trips_days_events
        Schema::table('trips_days_events', function (Blueprint $table) {
            $table->index('xid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex(['xid']);
        });

        Schema::table('events_reviews', function (Blueprint $table) {
            $table->dropIndex(['xid']);
        });

        Schema::table('trips_days_events', function (Blueprint $table) {
            $table->dropIndex(['xid']);
        });
    }
};
