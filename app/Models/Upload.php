<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    /** @use HasFactory<\Database\Factories\UploadFactory> */
    use HasFactory;

    protected $fillable = [
        'uploadable_type',
        'uploadable_id',
        'path',
        'original_name',
        'type',
        'size',
        'order',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'order' => 'integer',
        'size' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTO POLIMÓRFICO
    |--------------------------------------------------------------------------
    */
    public function uploadable()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getUrlAttribute(): string
    {
        return url("storage/{$this->path}");
    }
}
