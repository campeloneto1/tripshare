<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDayCity extends Model
{
    /** @use HasFactory<\Database\Factories\TripDayCityFactory> */
    use HasFactory;

    protected $table = 'trips_days_cities';


    protected $fillable = ['trip_day_id','city_name','lat','lon','osm_id','country_code','order'];

    public function events() {
        return $this->hasMany(TripDayEvent::class)->orderBy('order');
    }

    public function day() {
        return $this->belongsTo(TripDay::class, 'trip_day_id');
    }
}
