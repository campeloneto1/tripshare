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

    /**
     * Boot method para limpar cache do Trip quando TripDayEvent é modificado
     */
    protected static function booted(): void
    {
        static::saved(function (TripDayEvent $event) {
            $event->city->day->trip->clearSummaryCache();
        });

        static::deleted(function (TripDayEvent $event) {
            $event->city->day->trip->clearSummaryCache();
        });
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class, 'trip_day_event_id');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }
}
