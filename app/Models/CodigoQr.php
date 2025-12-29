<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class CodigoQr extends Model
{
    use HasFactory;

    protected $table = 'codigos_qr';

    protected $fillable = [
        'user_id',
        'departamento_id',
        'codigo',
        'token_encrypted',
        'fecha_generacion',
        'fecha_expiracion',
        'usado',
        'activo',
        'generado_por',
        'tipo',
        'uso_actual',
        'intentos_fallidos',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'usado' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Un código QR pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Departamento destino (solo aplica típicamente para QRs de visitantes)
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * Relación: Un código QR tiene muchos accesos
     */
    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class, 'codigo_qr_id');
    }

    /**
     * Relación: Un código QR puede estar autorizado para muchas puertas (reglas específicas por QR)
     */
    public function puertas(): BelongsToMany
    {
        return $this->belongsToMany(Puerta::class, 'codigo_qr_puerta_acceso', 'codigo_qr_id', 'puerta_id')
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
     * Verificar si el código QR está activo (no expirado y activo)
     */
    public function estaActivo(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $now = now();

        // Si tiene fecha de expiración y ya expiró, no está activo
        if ($this->fecha_expiracion && $this->fecha_expiracion->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Scope para obtener solo códigos QR activos
     */
    public function scopeActivos($query)
    {
        $now = now();
        return $query->where('activo', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>', $now);
            });
    }
    public function getTokenOriginalAttribute(): ?string
    {
        if (!$this->token_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->token_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Establecer el token original (se encripta automáticamente)
     */
    public function setTokenOriginal(string $token): void
    {
        $this->token_encrypted = Crypt::encryptString($token);
    }
}
