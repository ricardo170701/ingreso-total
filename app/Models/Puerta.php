<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puerta extends Model
{
    use HasFactory;

    protected $fillable = [
        // Asociar puerta a una zona (piso/área)
        'zona_id',
        'piso_id', // Foreign key a tabla pisos
        'tipo_puerta_id', // Foreign key a tabla tipo_puertas
        'material_id', // Foreign key a tabla materials
        'ip_entrada', // IP de la Raspberry Pi para entrada
        'ip_salida', // IP de la Raspberry Pi para salida
        'imagen', // Ruta de la imagen de la puerta
        'tiempo_apertura', // Tiempo en segundos que la puerta permanece abierta
        'alto', // Altura en centímetros
        'largo', // Largo en centímetros
        'ancho', // Ancho en centímetros
        'peso', // Peso en kilogramos
        'nombre',
        'ubicacion',
        'descripcion',
        // Identificador físico del lector/puerta (si aplica)
        'codigo_fisico',
        // Puerta especial (ej: discapacitados)
        'requiere_discapacidad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_discapacidad' => 'boolean',
        'tiempo_apertura' => 'integer',
        'alto' => 'decimal:2',
        'largo' => 'decimal:2',
        'ancho' => 'decimal:2',
        'peso' => 'decimal:2',
    ];

    /**
     * Relación: Una puerta pertenece a una zona
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    /**
     * Relación: Una puerta pertenece a un piso
     */
    public function piso(): BelongsTo
    {
        return $this->belongsTo(Piso::class);
    }

    /**
     * Relación: Una puerta pertenece a un tipo de puerta
     */
    public function tipoPuerta(): BelongsTo
    {
        return $this->belongsTo(TipoPuerta::class);
    }

    /**
     * Relación: Una puerta pertenece a un material
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Relación: Una puerta pertenece a muchos cargos (muchos a muchos)
     */
    public function cargos(): BelongsToMany
    {
        return $this->belongsToMany(Cargo::class, 'cargo_puerta_acceso')
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
     * Relación: Una puerta puede estar asociada a muchos códigos QR (reglas específicas por QR)
     */
    public function codigosQr(): BelongsToMany
    {
        return $this->belongsToMany(CodigoQr::class, 'codigo_qr_puerta_acceso', 'puerta_id', 'codigo_qr_id')
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
     * Relación: Una puerta tiene muchos accesos
     */
    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class);
    }

    /**
     * Relación: Una puerta tiene muchos mantenimientos
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class);
    }

    /**
     * Obtener el estado de mantenimiento de la puerta
     * Retorna: null (sin mantenimiento), 'programado' (amarillo), 'vencido' (rojo)
     */
    public function getEstadoMantenimientoAttribute(): ?string
    {
        // Si la relación no está cargada, cargarla
        if (!$this->relationLoaded('mantenimientos')) {
            $this->load('mantenimientos');
        }

        $hoy = now()->toDateString();

        // Primero verificar si hay mantenimientos programados activos (fecha_fin >= hoy)
        $mantenimientoProgramado = $this->mantenimientos
            ->where('tipo', 'programado')
            ->where('fecha_fin_programada', '>=', $hoy)
            ->sortByDesc('fecha_fin_programada')
            ->first();

        if ($mantenimientoProgramado) {
            return 'programado';
        }

        // Si no hay programados activos, verificar si hay vencidos (fecha_fin < hoy)
        $mantenimientoVencido = $this->mantenimientos
            ->where('tipo', 'programado')
            ->where('fecha_fin_programada', '<', $hoy)
            ->sortByDesc('fecha_fin_programada')
            ->first();

        if ($mantenimientoVencido) {
            return 'vencido';
        }

        return null;
    }
}
