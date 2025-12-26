<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MantenimientoImagen extends Model
{
    use HasFactory;

    protected $table = 'mantenimiento_imagenes';

    protected $fillable = [
        'mantenimiento_id',
        'ruta_imagen',
        'orden',
        'descripcion',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /**
     * Relación: Una imagen pertenece a un mantenimiento
     */
    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class);
    }
}
