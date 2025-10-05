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
        Schema::create('trips_days_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_day_city_id')->constrained('trips_days_cities')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['hotel','restaurant','attraction','transport','other']);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->string('xid')->nullable();
            $table->string('source_api')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('order')->default(1);
            $table->text('notes')->nullable();

            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('BRL');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips_days_events');
    }
};
