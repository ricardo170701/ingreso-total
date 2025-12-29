<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsMantenimientoDocumento extends Model
{
    use HasFactory;

    protected $table = 'ups_mantenimiento_documentos';

    protected $fillable = [
        'ups_mantenimiento_id',
        'ruta_documento',
        'nombre_original',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(UpsMantenimiento::class, 'ups_mantenimiento_id');
    }
}
