<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDayEvent extends Model
{
    /** @use HasFactory<\Database\Factories\TripDayEventsFactory> */
    use HasFactory;

    protected $table = 'trips_days_events';

    protected $fillable = [
        'trip_day_city_id','name','type','lat','lon','xid','source_api',
        'start_time','end_time','order','notes'
    ];

    public function city() {
        return $this->belongsTo(TripDayCity::class, 'trip_day_city_id');
    }
}
