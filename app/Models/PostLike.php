<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    /** @use HasFactory<\Database\Factories\PostLikeFactory> */
    use HasFactory;

    protected $table = 'posts_likes';

      protected $fillable = [
        'user_id',
        'post_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Boot method para limpar cache do Post quando like é criado/deletado
     */
    protected static function booted(): void
    {
        static::created(function (PostLike $like) {
            $like->post?->clearSummaryCache();
        });

        static::deleted(function (PostLike $like) {
            $like->post?->clearSummaryCache();
        });
    }
}
