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
        Schema::table('trips_days_events', function (Blueprint $table) {
            // Dropar índice antes de dropar a coluna
            $table->dropIndex('idx_trip_events_type');
        });

        Schema::table('trips_days_events', function (Blueprint $table) {
            // Adicionar place_id
            $table->foreignId('place_id')->nullable()->after('trip_day_city_id')->constrained('places')->onDelete('set null');

            // Remover campos que agora vêm do Place
            $table->dropColumn(['name', 'type', 'lat', 'lon', 'xid', 'source_api']);
        });

        // Atualizar events_reviews para usar place_id
        Schema::table('events_reviews', function (Blueprint $table) {
            // Dropar índice antes de dropar a coluna
            $table->dropIndex(['xid']);
        });

        Schema::table('events_reviews', function (Blueprint $table) {
            // Adicionar place_id
            $table->foreignId('place_id')->nullable()->after('trip_day_event_id')->constrained('places')->onDelete('cascade');

            // Remover xid
            $table->dropColumn('xid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips_days_events', function (Blueprint $table) {
            // Restaurar campos removidos
            $table->string('name')->after('trip_day_city_id');
            $table->enum('type', ['hotel','restaurant','attraction','transport','other'])->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->string('xid')->nullable();
            $table->string('source_api')->nullable();

            // Remover place_id
            $table->dropForeign(['place_id']);
            $table->dropColumn('place_id');
        });

        Schema::table('trips_days_events', function (Blueprint $table) {
            // Recriar índice
            $table->index('type', 'idx_trip_events_type');
        });

        Schema::table('events_reviews', function (Blueprint $table) {
            // Remover place_id
            $table->dropForeign(['place_id']);
            $table->dropColumn('place_id');
        });

        Schema::table('events_reviews', function (Blueprint $table) {
            // Restaurar xid com índice
            $table->string('xid')->nullable()->after('trip_day_event_id')->index();
        });
    }
};
