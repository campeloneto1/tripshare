<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    /** @use HasFactory<\Database\Factories\PostCommentFactory> */
    use HasFactory;

    protected $table = 'posts_comments';

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna resumo de métricas do comentário
     */
    public function getSummaryAttribute(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "post_comment_summary_{$this->id}",
            now()->addMinutes(30),
            fn() => [
                'replies_count' => $this->replies()->count(),
            ]
        );
    }

    /**
     * Retorna flags de estado do comentário
     */
    public function getFlagsAttribute(): array
    {
        return [
            'is_owner' => auth()->check() && $this->user_id === auth()->id(),
            'is_reply' => !is_null($this->parent_id),
            'has_replies' => $this->replies()->exists(),
        ];
    }

    /**
     * Limpa o cache do summary
     */
    public function clearSummaryCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget("post_comment_summary_{$this->id}");
    }

    /**
     * Boot method para limpar cache automaticamente
     */
    protected static function booted(): void
    {
        static::created(function (PostComment $comment) {
            $comment->clearSummaryCache();
            // Limpa cache do Post
            $comment->post?->clearSummaryCache();
            // Limpa cache do comentário pai se for uma resposta
            if ($comment->parent_id) {
                \Illuminate\Support\Facades\Cache::forget("post_comment_summary_{$comment->parent_id}");
            }
        });

        static::updated(function (PostComment $comment) {
            $comment->clearSummaryCache();
        });

        static::deleted(function (PostComment $comment) {
            $comment->clearSummaryCache();
            // Limpa cache do Post
            $comment->post?->clearSummaryCache();
            // Limpa cache do comentário pai
            if ($comment->parent_id) {
                \Illuminate\Support\Facades\Cache::forget("post_comment_summary_{$comment->parent_id}");
            }
        });
    }

}
