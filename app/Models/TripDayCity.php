<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDayCity extends Model
{
    /** @use HasFactory<\Database\Factories\TripDayCityFactory> */
    use HasFactory;

    protected $table = 'trips_days_cities';


    protected $fillable = [
        'trip_day_id',
        'city_name',
        'lat',
        'lon',
        'osm_id',
        'country_code',
        'order', 
        'created_by', 
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

    protected $with = ['events'];

    public function events() {
        return $this->hasMany(TripDayEvent::class)->orderBy('order');
    }

    public function day() {
        return $this->belongsTo(TripDay::class, 'trip_day_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function summary(){
        return [
            'total_events' => (int) $this->events->count(),
            'total_value' => (float) $this->events->sum('price'),
            'total_events_by_type' => [
                'hotel' => $this->events->where('type', 'hotel')->count(),
                'restaurant' => $this->events->where('type', 'restaurant')->count(),
                'attraction' => $this->events->where('type', 'attraction')->count(),
                'transport' => $this->events->where('type', 'transport')->count(),
                'other' => $this->events->where('type', 'other')->count(),
            ],
            'total_value_by_type' => [
                'hotel' => (float) $this->events->where('type', 'hotel')->sum('price'),
                'restaurant' => (float) $this->events->where('type', 'restaurant')->sum('price'),
                'attraction' => (float) $this->events->where('type', 'attraction')->sum('price'),
                'transport' => (float) $this->events->where('type', 'transport')->sum('price'),
                'other' => (float) $this->events->where('type', 'other')->sum('price'),
            ],
        ];
    }

}
