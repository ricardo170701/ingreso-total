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
        'gerencia_id',
        'responsable_id',
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
     * Gerencia destino (solo aplica típicamente para QRs de visitantes)
     */
    public function gerencia(): BelongsTo
    {
        return $this->belongsTo(Gerencia::class);
    }

    /**
     * Responsable del ingreso (usuario servidor público que autoriza el ingreso del visitante)
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
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
     * Para staff (servidor público/contratista): siempre verifica la fecha_expiracion del usuario
     * Para visitantes: verifica la fecha_expiracion del QR
     */
    public function estaActivo(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $now = now();

        // Cargar usuario y su rol si no está cargado
        if (!$this->relationLoaded('user')) {
            $this->load('user.role');
        }

        $user = $this->user;
        if (!$user) {
            return false;
        }

        $userRole = $user->role?->name ?? null;
        $staffRoles = ['servidor_publico', 'contratista', 'funcionario']; // 'funcionario' legado
        $isStaff = in_array($userRole, $staffRoles, true);

        if ($isStaff) {
            // Para staff: verificar siempre la fecha_expiracion del usuario, no la del QR
            if ($user->fecha_expiracion) {
                $fechaExpiracion = \Carbon\Carbon::parse($user->fecha_expiracion)->startOfDay();
                if ($fechaExpiracion->lt($now->startOfDay())) {
                    return false;
                }
            }
            // Si no tiene fecha_expiracion (contrato indefinido), el QR está activo mientras el usuario esté activo
            return true;
        } else {
            // Para visitantes: verificar la fecha_expiracion del QR
            if ($this->fecha_expiracion && $this->fecha_expiracion->lt($now)) {
                return false;
            }
            return true;
        }
    }

    /**
     * Scope para obtener solo códigos QR activos
     * Nota: Este scope solo filtra por activo=true y fecha_expiracion del QR.
     * Para staff, la validación completa (incluyendo fecha_expiracion del usuario) se hace en estaActivo()
     */
    public function scopeActivos($query)
    {
        $now = now();
        return $query->where('activo', true)
            ->where(function ($q) use ($now) {
                // Para visitantes: verificar fecha_expiracion del QR
                // Para staff: este filtro es preliminar, la validación real se hace en estaActivo()
                $q->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>', $now);
            })
            ->whereHas('user', function ($q) {
                // Asegurar que el usuario esté activo
                $q->where('activo', true);
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
