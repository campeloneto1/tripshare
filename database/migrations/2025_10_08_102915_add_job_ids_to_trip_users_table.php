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
        Schema::table('trips_users', function (Blueprint $table) {
            $table->string('checkin_reminder_job_id')->nullable()->comment('ID do job de lembrete de check-in');
            $table->string('transport_reminder_job_id')->nullable()->comment('ID do job de lembrete de transporte 2h antes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips_users', function (Blueprint $table) {
            $table->dropColumn(['checkin_reminder_job_id', 'transport_reminder_job_id']);
        });
    }
};
