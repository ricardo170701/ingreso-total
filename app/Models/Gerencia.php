<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gerencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'secretaria_id',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación: Una gerencia pertenece a una secretaría
     */
    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    /**
     * Relación: Una gerencia tiene muchos usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación: Una gerencia tiene muchos códigos QR (para visitantes)
     */
    public function codigosQr(): HasMany
    {
        return $this->hasMany(CodigoQr::class);
    }
}