<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsBitacoraImagen extends Model
{
    use HasFactory;

    protected $table = 'ups_vitacora_imagenes';

    protected $fillable = [
        'ups_vitacora_id',
        'ruta_imagen',
        'orden',
        'descripcion',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function bitacora(): BelongsTo
    {
        return $this->belongsTo(UpsBitacora::class, 'ups_vitacora_id');
    }
}
