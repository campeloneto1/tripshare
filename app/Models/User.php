<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'nome',
        'email',
        'username',
        'cpf',
        'telefone',
        'password',
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
            'nome' => 'string',
            'email' => 'string',
            'telefone' => 'string',
            'cpf' => 'string',
            'username' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'perfil_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected $with = ['perfil'];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }

    public function hasPermissao(string $nomePermissao): bool
    {
        return $this->perfil && $this->perfil->permissoes->contains('nome', $nomePermissao);
    }
}
