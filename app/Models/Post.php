<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'trip_id',
        'shared_post_id',
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

    protected $with = ['user', 'trip', 'sharedPost'];

    /**
     * Usuário autor do post
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Viagem associada ao post (opcional)
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Post original, caso este seja um compartilhamento
     */
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    /**
     * Posts que compartilharam este post
     */
    public function sharedBy()
    {
        return $this->hasMany(Post::class, 'shared_post_id');
    }

    /**
     * Uploads (imagens e vídeos) associados ao post
     */
    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    /**
     * Comentários do post (caso exista funcionalidade)
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Curtidas do post (caso exista funcionalidade)
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES E ATRIBUTOS CUSTOMIZADOS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna apenas posts públicos (exemplo)
     */
    public function scopePublic($query)
    {
        return $query->whereNull('trip_id'); // ou outro critério, se tiver campo `is_public`
    }

    /**
     * Retorna se o post é compartilhado
     */
    public function getIsSharedAttribute(): bool
    {
        return !is_null($this->shared_post_id);
    }

    /**
     * Retorna o tipo principal do post
     */
    public function getTypeAttribute(): string
    {
        if ($this->is_shared) {
            return 'shared';
        }

        if ($this->trip_id) {
            return 'trip';
        }

        return 'regular';
    }
    /**
     * Retorna resumo de métricas do post
     */
    public function getSummaryAttribute(): array
    {
        return [
            'likes_count' => $this->likes_count ?? $this->likes()->count(),
            'comments_count' => $this->comments_count ?? $this->comments()->count(),
            'shares_count' => $this->shared_by_count ?? $this->sharedBy()->count(),
            //'uploads_count' => $this->uploads_count ?? $this->uploads()->count(),
        ];
    }

    /**
     * Retorna flags de estado do post
     */
    public function getFlagsAttribute(): array
    {
        return [
            'is_owner' => auth()->check() && $this->user_id === auth()->id(),
            'liked_by_user' => auth()->check()
                ? $this->likes()->where('user_id', auth()->id())->exists()
                : false,
            'is_shared' => $this->is_shared,
        ];
    }
}
