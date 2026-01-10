<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Secretaria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'piso_id',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación: Una secretaría pertenece a un piso
     */
    public function piso(): BelongsTo
    {
        return $this->belongsTo(Piso::class);
    }

    /**
     * Relación: Una secretaría tiene muchos usuarios a través de sus gerencias
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Gerencia::class);
    }

    /**
     * Relación: Una secretaría tiene muchas gerencias
     */
    public function gerencias(): HasMany
    {
        return $this->hasMany(Gerencia::class);
    }
}