<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarjetaNfc extends Model
{
    use HasFactory;

    protected $table = 'tarjetas_nfc';

    protected $fillable = [
        'codigo',
        'nombre',
        'user_id',
        'gerencia_id',
        'fecha_asignacion',
        'fecha_expiracion',
        'activo',
        'asignado_por',
        'observaciones',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Una tarjeta NFC pertenece a un usuario (visitante)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gerencia destino (solo aplica típicamente para tarjetas de visitantes)
     */
    public function gerencia(): BelongsTo
    {
        return $this->belongsTo(Gerencia::class);
    }

    /**
     * Usuario que asignó la tarjeta
     */
    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    /**
     * Relación: Una tarjeta NFC puede estar autorizada para muchas puertas (reglas específicas por tarjeta)
     */
    public function puertas(): BelongsToMany
    {
        return $this->belongsToMany(Puerta::class, 'tarjeta_nfc_puerta_acceso', 'tarjeta_nfc_id', 'puerta_id')
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
     * Relación: Una tarjeta NFC tiene muchos accesos
     */
    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class, 'tarjeta_nfc_id');
    }

    /**
     * Historial de asignaciones/desasignaciones de la tarjeta
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(TarjetaNfcAsignacion::class, 'tarjeta_nfc_id');
    }

    /**
     * Verificar si la tarjeta NFC está activa (no expirada y activo)
     */
    public function estaActiva(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $now = now();

        // Si tiene fecha de expiración y ya expiró, no está activa
        if ($this->fecha_expiracion && $this->fecha_expiracion->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Scope para obtener solo tarjetas NFC activas
     */
    public function scopeActivas($query)
    {
        $now = now();
        return $query->where('activo', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>', $now);
            });
    }
}
