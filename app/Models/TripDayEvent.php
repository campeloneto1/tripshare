<?php

namespace App\Models;

use App\Traits\LogsTripHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TripDayEvent extends Model
{
    /** @use HasFactory<\Database\Factories\TripDayEventsFactory> */
    use HasFactory, LogsTripHistory;

    protected $table = 'trips_days_events';

    protected $fillable = [
        'trip_day_city_id','place_id',
        'start_time','end_time','order','notes', 'price', 'currency'
    ];

    public function place() {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function city() {
        return $this->belongsTo(TripDayCity::class, 'trip_day_city_id');
    }

    public function tripDay() {
        return $this->hasOneThrough(TripDay::class, TripDayCity::class, 'id', 'id', 'trip_day_city_id', 'trip_day_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class, 'trip_day_event_id');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna resumo de métricas do evento
     */
    public function getSummaryAttribute(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "trip_day_event_summary_{$this->id}",
            now()->addMinutes(30),
            fn() => [
                'reviews_count' => $this->reviews()->count(),
                'average_rating' => round($this->averageRating() ?? 0, 1),
                'duration_minutes' => $this->start_time && $this->end_time
                    ? \Carbon\Carbon::parse($this->start_time)->diffInMinutes(\Carbon\Carbon::parse($this->end_time))
                    : null,
            ]
        );
    }

    /**
     * Retorna flags de estado do evento
     */
    public function getFlagsAttribute(): array
    {
        $trip = $this->city?->day?->trip;

        return [
            'has_price' => !is_null($this->price) && $this->price > 0,
            'has_time' => !is_null($this->start_time),
            'has_reviews' => $this->reviews()->exists(),
            'can_edit' => auth()->check() && $trip && (
                $trip->user_id === auth()->id() ||
                $trip->users()->where('user_id', auth()->id())->whereIn('role', ['admin'])->exists()
            ),
        ];
    }

    /**
     * Limpa o cache do summary
     */
    public function clearSummaryCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget("trip_day_event_summary_{$this->id}");
    }

    /**
     * Boot method para limpar cache do Trip quando TripDayEvent é modificado
     */
    protected static function booted(): void
    {
        static::saved(function (TripDayEvent $event) {
            $event->clearSummaryCache();
            $event->city->day->trip->clearSummaryCache();
        });

        static::deleted(function (TripDayEvent $event) {
            $event->clearSummaryCache();
            $event->city->day->trip->clearSummaryCache();
        });
    }
}
