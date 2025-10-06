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
        Schema::create('events_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_day_event_id')->nullable()->constrained('trips_days_events')->onDelete('cascade');
            $table->string('xid')->nullable()->index(); // id da API externa
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->comment('0–5');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['trip_day_event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events_reviews');
    }
};
