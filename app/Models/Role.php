<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relación: Un rol tiene muchos usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación: Un rol tiene muchos permisos
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        // Super usuario tiene todos los permisos
        if ($this->name === 'super_usuario') {
            return true;
        }

        return $this->permissions()
            ->where('name', $permissionName)
            ->where('activo', true)
            ->exists();
    }
}
