<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReview extends Model
{
    /** @use HasFactory<\Database\Factories\EventReviewFactory> */
    use HasFactory;

    protected $table = 'events_reviews';

    protected $fillable = [
        'trip_day_event_id',
        'place_id',
        'user_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'rating' => 'integer',
        'trip_day_event_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Relacionamento com o evento.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(TripDayEvent::class, 'trip_day_event_id');
    }

    /**
     * Relacionamento com o usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o lugar.
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}
