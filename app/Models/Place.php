<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $table = 'places';

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'lat',
        'lon',
        'xid',
        'source_api',
        'type'
    ];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
    ];

    public function events()
    {
        return $this->hasMany(TripDayEvent::class, 'place_id');
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class, 'place_id');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function reviewsCount()
    {
        return $this->reviews()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna resumo de métricas do local
     */
    public function getSummaryAttribute(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "place_summary_{$this->id}",
            now()->addHours(1),
            fn() => [
                'reviews_count' => $this->reviewsCount(),
                'average_rating' => round($this->averageRating() ?? 0, 1),
                'events_count' => $this->events()->count(),
                'trips_count' => $this->events()
                    ->join('trips_days_cities', 'trips_days_events.trip_day_city_id', '=', 'trips_days_cities.id')
                    ->join('trips_days', 'trips_days_cities.trip_day_id', '=', 'trips_days.id')
                    ->distinct('trips_days.trip_id')
                    ->count('trips_days.trip_id'),
            ]
        );
    }

    /**
     * Retorna flags de estado do local
     */
    public function getFlagsAttribute(): array
    {
        return [
            'has_coordinates' => !is_null($this->lat) && !is_null($this->lon),
            'has_reviews' => $this->reviews()->exists(),
            'is_from_api' => !is_null($this->source_api),
            'has_complete_address' => !is_null($this->address) && !is_null($this->city),
        ];
    }

    /**
     * Limpa o cache do summary
     */
    public function clearSummaryCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget("place_summary_{$this->id}");
    }

    /**
     * Boot method para limpar cache automaticamente
     */
    protected static function booted(): void
    {
        static::updated(function (Place $place) {
            $place->clearSummaryCache();
        });

        static::deleted(function (Place $place) {
            $place->clearSummaryCache();
        });
    }
}
