<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolRunItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_run_id',
        'puerta_id',
        'ip',
        'tipo_ip',
        'estado',
        'intentos',
        'http_status',
        'respuesta',
        'error',
        'enviado_at',
        'completado_at',
    ];

    protected $casts = [
        'intentos' => 'integer',
        'http_status' => 'integer',
        'enviado_at' => 'datetime',
        'completado_at' => 'datetime',
    ];

    /**
     * Relación: Un item pertenece a un protocol run
     */
    public function protocolRun(): BelongsTo
    {
        return $this->belongsTo(ProtocolRun::class);
    }

    /**
     * Relación: Un item pertenece a una puerta
     */
    public function puerta(): BelongsTo
    {
        return $this->belongsTo(Puerta::class);
    }
}
