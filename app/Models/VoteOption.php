<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoteOption extends Model
{
    /** @use HasFactory<\Database\Factories\VoteOptionFactory> */
    use HasFactory;

    protected $table = 'votes_options';

    protected $fillable = [
        'vote_question_id',
        'place_id',
        'title',
        'json_data',
    ];

    protected $casts = [
        'json_data' => 'array',
    ];

    // Relação com a pergunta
    public function question(): BelongsTo
    {
        return $this->belongsTo(VoteQuestion::class, 'vote_question_id');
    }

    // Relação com o place (para votos de eventos)
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    // Relação com os votos
    public function votes(): HasMany
    {
        return $this->hasMany(VoteAnswer::class);
    }
}
