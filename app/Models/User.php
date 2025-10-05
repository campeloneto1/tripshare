<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'cpf',
        'phone',
        'password',
        'role_id',
        'is_public',
        'avatar',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'cpf' => 'string',
            'username' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
            'is_public' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected $with = ['role'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->role && $this->role->permissions->contains('name', $permissionName);
    }

    public function is_admin(): bool
    {
        return $this->role && $this->role->id === 1;
    }

    public function trips(){
        return $this->hasMany(Trip::class)->without('user')->orderBy('id', 'desc');
    }
    
    public function tripsParticipating() {
        return $this->belongsToMany(Trip::class, 'trips_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // Relacionamento com a tabela pivot UserFollow
    public function followerRelations()
    {
        return $this->hasMany(UserFollow::class, 'following_id');
    }

    public function followingRelations()
    {
        return $this->hasMany(UserFollow::class, 'follower_id');
    }

    // Helpers para acessar os usuários diretamente
    public function followers()
    {
        return $this->belongsToMany(User::class, 'users_follows', 'following_id', 'follower_id')
                    ->wherePivot('status', 'accepted')
                    ->withPivot('status', 'accepted_at')
                    ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'users_follows', 'follower_id', 'following_id')
                    ->wherePivot('status', 'accepted')
                    ->withPivot('status', 'accepted_at')
                    ->withTimestamps();
    }

    public function pendingFollowRequests()
    {
        return $this->hasMany(UserFollow::class, 'following_id')
                    ->where('status', 'pending');
    }

    public function sentFollowRequests()
    {
        return $this->hasMany(UserFollow::class, 'follower_id')
                    ->where('status', 'pending');
    }

    public function getAvatar()
{
        if ($this->avatar) {
            return url("storage/avatars/{$this->avatar}");
        }

        // Gera o nome para o fallback
        $name = urlencode($this->name ?? 'User');

        // Gera o avatar com as iniciais (usando ui-avatars.com)
        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff&size=256";
    }

    public function summary(){
        return [
            'following' => $this->following()->count(),
            'followers' => $this->followers()->count(),
            'trips' => $this->trips()->count(),
            'tripsParticipating' => $this->tripsParticipating()->count()
        ];
    }

    public function flags(){
        $isOwner = auth()->check() && auth()->user()->id === $this->id;

        return [
            'is_admin' => $this->is_admin(),
            'is_owner' => $isOwner,
            'is_following' => !$isOwner && auth()->check() && auth()->user()->following()->where('following_id', $this->id)->exists(),
            'is_followed_by' => !$isOwner && auth()->check() && $this->following()->where('following_id', auth()->user()->id)->exists(),
            'has_pending_follow_request' => !$isOwner && auth()->check() && auth()->user()->sentFollowRequests()->where('following_id', $this->id)->exists(),
            'has_received_follow_request' => !$isOwner && auth()->check() && $this->pendingFollowRequests()->where('follower_id', auth()->user()->id)->exists(),
        ];
    }
}
