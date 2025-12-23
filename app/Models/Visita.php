<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Visita extends Model
{
    use HasFactory;

    protected $table = 'visitas_registradas';

    protected $fillable = [
        'visitante_id',
        'empleado_anfitrion',
        'operador_registro',
        'motivo_visita',
        'fecha_entrada_estimada',
        'fecha_salida_estimada',
        'fecha_entrada_real',
        'fecha_salida_real',
        'estado',
        'qr_generado',
    ];

    protected $casts = [
        'fecha_entrada_estimada' => 'datetime',
        'fecha_salida_estimada' => 'datetime',
        'fecha_entrada_real' => 'datetime',
        'fecha_salida_real' => 'datetime',
        'qr_generado' => 'boolean',
    ];

    public function visitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visitante_id');
    }

    public function anfitrion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empleado_anfitrion');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_registro');
    }

    /**
     * Puertas autorizadas para esta visita (en vez de JSON)
     */
    public function puertas(): BelongsToMany
    {
        return $this->belongsToMany(Puerta::class, 'visita_puerta', 'visita_id', 'puerta_id')
            ->withTimestamps();
    }
}
