<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viagem extends Model
{
    /** @use HasFactory<\Database\Factories\ViagemFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'viagens';

    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
