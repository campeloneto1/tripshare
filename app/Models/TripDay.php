<?php

namespace App\Models;

use App\Traits\LogsTripHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TripDay extends Model
{
    /** @use HasFactory<\Database\Factories\TripDayFactory> */
    use HasFactory, LogsTripHistory;

    protected $table = 'trips_days';

    protected $fillable = [
        'trip_id',
        'date',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $with = ['cities'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

     public function cities() {
        return $this->hasMany(TripDayCity::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Retorna resumo de métricas do dia
     */
    public function getSummaryAttribute(): array
    {
        $allEvents = $this->cities->flatMap(fn($city) => $city->events);

        return [
            'total_cities' => (int) $this->cities->count(),
            'total_events' => (int) $allEvents->count(),
            'total_value' => (float) $allEvents->sum('price'),
            'total_events_by_type' => [
                'hotel' => $allEvents->where('type', 'hotel')->count(),
                'restaurant' => $allEvents->where('type', 'restaurant')->count(),
                'attraction' => $allEvents->where('type', 'attraction')->count(),
                'transport' => $allEvents->where('type', 'transport')->count(),
                'other' => $allEvents->where('type', 'other')->count(),
            ],
            'total_value_by_type' => [
                'hotel' => (float) $allEvents->where('type', 'hotel')->sum('price'),
                'restaurant' => (float) $allEvents->where('type', 'restaurant')->sum('price'),
                'attraction' => (float) $allEvents->where('type', 'attraction')->sum('price'),
                'transport' => (float) $allEvents->where('type', 'transport')->sum('price'),
                'other' => (float) $allEvents->where('type', 'other')->sum('price'),
            ],
        ];
    }

    /**
     * Boot method para limpar cache do Trip quando TripDay é modificado
     */
    protected static function booted(): void
    {
        static::saved(function (TripDay $tripDay) {
            $tripDay->trip->clearSummaryCache();
        });

        static::deleted(function (TripDay $tripDay) {
            $tripDay->trip->clearSummaryCache();
        });
    }
}
