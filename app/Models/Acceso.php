<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acceso extends Model
{
    use HasFactory;

    protected $table = 'accesos_registrados';

    protected $fillable = [
        'user_id',
        'puerta_id',
        'codigo_qr_id',
        'tarjeta_nfc_id',
        'tipo_evento',
        'fecha_acceso',
        'permitido',
        'lector_id',
        'dispositivo_id',
        'ubicacion_lector',
        'motivo_denegacion',
        'observaciones',
        'fotografia_captura',
        'temperatura',
    ];

    protected $casts = [
        'fecha_acceso' => 'datetime',
        'permitido' => 'boolean',
        'temperatura' => 'decimal:2',
    ];

    /**
     * Relación: Un acceso pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un acceso pertenece a una puerta
     */
    public function puerta(): BelongsTo
    {
        return $this->belongsTo(Puerta::class);
    }

    /**
     * Relación: Un acceso pertenece a un código QR
     */
    public function codigoQr(): BelongsTo
    {
        return $this->belongsTo(CodigoQr::class, 'codigo_qr_id');
    }

    /**
     * Relación: Un acceso pertenece a una tarjeta NFC
     */
    public function tarjetaNfc(): BelongsTo
    {
        return $this->belongsTo(TarjetaNfc::class, 'tarjeta_nfc_id');
    }
}
