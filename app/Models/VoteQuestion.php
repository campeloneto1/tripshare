<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VoteQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\VoteQuestionFactory> */
    use HasFactory;

    protected $table = 'votes_questions';

    protected $fillable = [
        'title',
        'type',
        'start_at',
        'end_at',
        'is_closed',
        'votable_id',
        'votable_type',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_closed' => 'boolean',
    ];

    // Relação morph para saber para qual entidade a votação se aplica
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }

    // Opções da votação
    public function options(): HasMany
    {
        return $this->hasMany(VoteOption::class);
    }
}
