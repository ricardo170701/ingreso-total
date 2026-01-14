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
        'tiempo_discapacitados', // Tiempo en segundos que la puerta permanece abierta para personas discapacitadas
        'alto', // Altura en centímetros
        'largo', // Largo en centímetros
        'ancho', // Ancho en centímetros
        'peso', // Peso en kilogramos
        'nombre',
        'ubicacion',
        'descripcion',
        // Identificador físico del lector/puerta (si aplica)
        'codigo_fisico', // Raspberry/lector de entrada
        'codigo_fisico_salida', // Raspberry/lector de salida
        // Puerta especial (ej: discapacitados)
        'requiere_discapacidad',
        'es_oculta', // Puerta oculta (solo visible con permiso)
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_discapacidad' => 'boolean',
        'es_oculta' => 'boolean',
        'tiempo_apertura' => 'integer',
        'tiempo_discapacitados' => 'integer',
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
     * Relación: Una puerta puede estar asociada a muchas tarjetas NFC (reglas específicas por tarjeta)
     */
    public function tarjetasNfc(): BelongsToMany
    {
        return $this->belongsToMany(TarjetaNfc::class, 'tarjeta_nfc_puerta_acceso', 'puerta_id', 'tarjeta_nfc_id')
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

        // Para "programado" usamos fecha_fin_programada como fecha límite (fallback: fecha_mantenimiento)
        $programados = $this->mantenimientos->where('tipo', 'programado');

        $mantenimientoProgramado = $programados
            ->filter(function ($m) use ($hoy) {
                $due = $m->fecha_fin_programada?->toDateString() ?? $m->fecha_mantenimiento?->toDateString();
                return $due && $due >= $hoy;
            })
            ->sortByDesc(function ($m) {
                return $m->fecha_fin_programada?->toDateString() ?? $m->fecha_mantenimiento?->toDateString() ?? '';
            })
            ->first();

        if ($mantenimientoProgramado) {
            return 'programado';
        }

        // Si no hay programados activos, verificar vencidos (fecha límite < hoy)
        $mantenimientoVencido = $programados
            ->filter(function ($m) use ($hoy) {
                $due = $m->fecha_fin_programada?->toDateString() ?? $m->fecha_mantenimiento?->toDateString();
                return $due && $due < $hoy;
            })
            ->sortByDesc(function ($m) {
                return $m->fecha_fin_programada?->toDateString() ?? $m->fecha_mantenimiento?->toDateString() ?? '';
            })
            ->first();

        if ($mantenimientoVencido) {
            return 'vencido';
        }

        return null;
    }

    /**
     * Verificar si la puerta está conectada (responde a conexión)
     * Retorna: true si está conectada, false si no
     * @deprecated Usar estaConectadaEntrada() o estaConectadaSalida() en su lugar
     */
    public function estaConectada(): bool
    {
        // Si no tiene IP configurada, no puede estar conectada
        $ip = $this->ip_entrada ?? $this->ip_salida;
        if (!$ip) {
            return false;
        }

        // Intentar conexión TCP al puerto 8000 (puerto común para servicios en Raspberry Pi)
        // Con un timeout corto de 2 segundos
        $puerto = 8000;
        $timeout = 2;

        $conexion = @fsockopen($ip, $puerto, $errno, $errstr, $timeout);

        if ($conexion) {
            fclose($conexion);
            return true;
        }

        return false;
    }

    /**
     * Verificar si la conexión de entrada está activa
     * Retorna: true si está conectada, false si no, null si no tiene IP de entrada
     */
    public function estaConectadaEntrada(): ?bool
    {
        if (!$this->ip_entrada) {
            return null;
        }

        $puerto = 8000;
        $timeout = 2;

        $conexion = @fsockopen($this->ip_entrada, $puerto, $errno, $errstr, $timeout);

        if ($conexion) {
            fclose($conexion);
            return true;
        }

        return false;
    }

    /**
     * Verificar si la conexión de salida está activa
     * Retorna: true si está conectada, false si no, null si no tiene IP de salida
     */
    public function estaConectadaSalida(): ?bool
    {
        if (!$this->ip_salida) {
            return null;
        }

        $puerto = 8000;
        $timeout = 2;

        $conexion = @fsockopen($this->ip_salida, $puerto, $errno, $errstr, $timeout);

        if ($conexion) {
            fclose($conexion);
            return true;
        }

        return false;
    }

    /**
     * Accessor para obtener el estado de conexión
     * @deprecated Usar getConexionEntradaAttribute o getConexionSalidaAttribute
     */
    public function getEstaConectadaAttribute(): bool
    {
        return $this->estaConectada();
    }
}
