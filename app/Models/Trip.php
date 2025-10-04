<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'trips';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'initial_date',
        'final_date',
    ];

    protected $casts = [
        'initial_date' => 'date',
        'final_date' => 'date',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
