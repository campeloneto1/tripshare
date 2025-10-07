<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\VoteAnswerFactory> */
    use HasFactory;

    protected $table = 'votes_answers';

    protected $fillable = [
        'vote_option_id',
        'user_id',
    ];

    // Relação com a opção votada
    public function option(): BelongsTo
    {
        return $this->belongsTo(VoteOption::class, 'vote_option_id');
    }

    // Relação com o usuário que votou
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
