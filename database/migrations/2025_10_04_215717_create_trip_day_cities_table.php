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
        Schema::create('trips_days_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_day_id')->constrained()->onDelete('cascade');
            $table->string('city_name');
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->string('osm_id')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->integer('order')->default(1);

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips_days_cities');
    }
};
