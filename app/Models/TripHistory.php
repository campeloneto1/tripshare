<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripHistory extends Model
{
    protected $table = 'trips_histories';

    protected $fillable = [
        'trip_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'changes',
        'description',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }
}
