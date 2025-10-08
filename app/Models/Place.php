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
}
