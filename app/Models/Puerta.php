<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puerta extends Model
{
    use HasFactory;

    protected $fillable = [
        // Futuro: asociar puerta a una zona (piso/área)
        'zona_id',
        'nombre',
        'ubicacion',
        'descripcion',
        // Identificador físico del lector/puerta (si aplica)
        'codigo_fisico',
        // Puerta especial (ej: discapacitados)
        'requiere_discapacidad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_discapacidad' => 'boolean',
    ];

    /**
     * Relación: Una puerta pertenece a una zona
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    /**
     * Relación: Una puerta pertenece a muchos cargos (muchos a muchos)
     */
    public function cargos(): BelongsToMany
    {
        return $this->belongsToMany(Cargo::class, 'cargo_puerta_acceso')
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
     * Relación: Una puerta puede estar asociada a muchos códigos QR (reglas específicas por QR)
     */
    public function codigosQr(): BelongsToMany
    {
        return $this->belongsToMany(CodigoQr::class, 'codigo_qr_puerta_acceso', 'puerta_id', 'codigo_qr_id')
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
     * Relación: Una puerta tiene muchos accesos
     */
    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class);
    }
}
