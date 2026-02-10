<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarjetaNfcAsignacion extends Model
{
    use HasFactory;

    protected $table = 'tarjeta_nfc_asignaciones';

    protected $fillable = [
        'tarjeta_nfc_id',
        'user_id',
        'asignado_por',
        'gerencia_id',
        'secretaria_id',
        'responsable_id',
        'fecha_asignacion',
        'fecha_desasignacion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_desasignacion' => 'datetime',
    ];

    public function tarjetaNfc(): BelongsTo
    {
        return $this->belongsTo(TarjetaNfc::class, 'tarjeta_nfc_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    public function gerencia(): BelongsTo
    {
        return $this->belongsTo(Gerencia::class);
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
