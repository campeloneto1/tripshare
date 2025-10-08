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

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna resumo da avaliação
     */
    public function getSummaryAttribute(): array
    {
        return [
            'has_comment' => !is_null($this->comment) && trim($this->comment) !== '',
            'rating_stars' => $this->rating,
        ];
    }

    /**
     * Retorna flags de estado da avaliação
     */
    public function getFlagsAttribute(): array
    {
        return [
            'is_owner' => auth()->check() && $this->user_id === auth()->id(),
            'has_high_rating' => $this->rating >= 4,
            'has_low_rating' => $this->rating <= 2,
            'has_comment' => !is_null($this->comment) && trim($this->comment) !== '',
        ];
    }

    /**
     * Boot method para limpar cache automaticamente
     */
    protected static function booted(): void
    {
        static::saved(function (EventReview $review) {
            // Limpa cache do evento
            if ($review->event) {
                $review->event->clearSummaryCache();
            }
            // Limpa cache do place
            if ($review->place) {
                $review->place->clearSummaryCache();
            }
        });

        static::deleted(function (EventReview $review) {
            if ($review->event) {
                $review->event->clearSummaryCache();
            }
            if ($review->place) {
                $review->place->clearSummaryCache();
            }
        });
    }
}
