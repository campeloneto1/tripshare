<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripUser extends Model
{
    /** @use HasFactory<\Database\Factories\TripUserFactory> */
    use HasFactory;

    protected $table = 'trips_users';

    protected $fillable = [
        'trip_id',
        'user_id',
        'role',
    ];

    public function trip() {
        return $this->belongsTo(Trip::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
