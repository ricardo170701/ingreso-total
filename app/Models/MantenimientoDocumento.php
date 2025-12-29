<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MantenimientoDocumento extends Model
{
    use HasFactory;

    protected $table = 'mantenimiento_documentos';

    protected $fillable = [
        'mantenimiento_id',
        'ruta_documento',
        'nombre_original',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /**
     * Relación: Un documento pertenece a un mantenimiento
     */
    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class);
    }
}
