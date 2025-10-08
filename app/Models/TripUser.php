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

    

}
