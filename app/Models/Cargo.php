<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'activo',
        'requiere_permiso_superior',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_permiso_superior' => 'boolean',
    ];

    /**
     * Relación: Un cargo tiene muchos usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación: Un cargo tiene muchas puertas (permiso por puerta individual)
     */
    public function puertas(): BelongsToMany
    {
        return $this->belongsToMany(Puerta::class, 'cargo_puerta_acceso')
            ->withPivot([
                'hora_inicio',
                'hora_fin',
                'dias_semana',
                'fecha_inicio',
                'fecha_fin',
                'activo',
            ])
            ->withTimestamps();
    }

    /**
     * Relación: Un cargo tiene muchos pisos (muchos a muchos)
     */
    public function pisos(): BelongsToMany
    {
        return $this->belongsToMany(Piso::class, 'cargo_piso_acceso')
            ->withPivot([
                'hora_inicio',
                'hora_fin',
                'dias_semana',
                'fecha_inicio',
                'fecha_fin',
                'activo',
            ])
            ->withTimestamps();
    }

    /**
     * Relación: Un cargo tiene muchos permisos del sistema
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'cargo_permission')
            ->withTimestamps();
    }

    /**
     * Relación: historial de cambios del cargo (auditoría)
     */
    public function historial(): HasMany
    {
        return $this->hasMany(CargoHistorial::class)->orderByDesc('created_at');
    }

    /**
     * Verificar si el cargo tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('name', $permissionName)
            ->where('activo', true)
            ->exists();
    }
}
