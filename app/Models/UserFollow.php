<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    /** @use HasFactory<\Database\Factories\UserFollowFactory> */
    use HasFactory;

    protected $table = 'users_follows';

     protected $fillable = [
        'follower_id',
        'following_id',
        'status',
        'accepted_at',

    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    /**
     * Boot method para limpar cache dos usuários quando follow é criado/alterado/deletado
     */
    protected static function booted(): void
    {
        static::created(function (UserFollow $follow) {
            $follow->follower?->clearSummaryCache();
            $follow->following?->clearSummaryCache();
        });

        static::updated(function (UserFollow $follow) {
            $follow->follower?->clearSummaryCache();
            $follow->following?->clearSummaryCache();
        });

        static::deleted(function (UserFollow $follow) {
            $follow->follower?->clearSummaryCache();
            $follow->following?->clearSummaryCache();
        });
    }
}
