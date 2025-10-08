<?php

namespace App\Models;

use App\Traits\LogsTripHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripUser extends Model
{
    /** @use HasFactory<\Database\Factories\TripUserFactory> */
    use HasFactory, LogsTripHistory;

    protected $table = 'trips_users';

    protected $fillable = [
        'trip_id',
        'user_id',
        'role',
        'transport_type',
        'transport_datetime',
        'checkin_reminder_job_id',
        'transport_reminder_job_id',
    ];

    protected $casts = [
        'transport_datetime' => 'datetime',
    ];

    public function trip() {
        return $this->belongsTo(Trip::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna resumo de informações do participante
     */
    public function getSummaryAttribute(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "trip_user_summary_{$this->id}",
            now()->addMinutes(30),
            fn() => [
                'has_transport' => !is_null($this->transport_type),
                'has_reminders' => !is_null($this->checkin_reminder_job_id) || !is_null($this->transport_reminder_job_id),
                'days_until_trip' => $this->trip?->start_date
                    ? now()->diffInDays($this->trip->start_date, false)
                    : null,
            ]
        );
    }

    /**
     * Retorna flags de estado do participante na viagem
     */
    public function getFlagsAttribute(): array
    {
        return [
            'is_admin' => $this->role === 'admin',
            'is_participant' => $this->role === 'participant',
            'is_owner' => $this->trip && $this->trip->user_id === $this->user_id,
            'has_transport_info' => !is_null($this->transport_type) && !is_null($this->transport_datetime),
            'has_checkin_reminder' => !is_null($this->checkin_reminder_job_id),
            'has_transport_reminder' => !is_null($this->transport_reminder_job_id),
        ];
    }

    /**
     * Limpa o cache do summary
     */
    public function clearSummaryCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget("trip_user_summary_{$this->id}");
    }

    /**
     * Boot method para limpar cache automaticamente
     */
    protected static function booted(): void
    {
        static::saved(function (TripUser $tripUser) {
            $tripUser->clearSummaryCache();
        });

        static::deleted(function (TripUser $tripUser) {
            $tripUser->clearSummaryCache();
        });
    }

}
