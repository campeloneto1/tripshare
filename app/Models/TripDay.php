<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDay extends Model
{
    /** @use HasFactory<\Database\Factories\TripDayFactory> */
    use HasFactory;

    protected $table = 'trips_days';

    protected $fillable = [
        'trip_id',
        'date',
        'created_by',
        'updated_by'
    ];

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
}
