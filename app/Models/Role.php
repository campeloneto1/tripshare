<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions', 'role_id', 'permission_id');

    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
