<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodigoQr extends Model
{
    use HasFactory;

    protected $table = 'codigos_qr';

    protected $fillable = [
        'user_id',
        'codigo',
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
}
