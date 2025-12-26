<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'puerta_id',
        'usuario_id',
        'otros_defectos',
        'observaciones',
        'fecha_mantenimiento',
        'tipo',
        'fecha_fin_programada',
    ];

    protected $casts = [
        'fecha_mantenimiento' => 'date',
        'fecha_fin_programada' => 'date',
    ];

    /**
     * Relación: Un mantenimiento pertenece a una puerta
     */
    public function puerta(): BelongsTo
    {
        return $this->belongsTo(Puerta::class);
    }

    /**
     * Relación: Un mantenimiento pertenece a un usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación: Un mantenimiento puede tener muchos defectos
     */
    public function defectos(): BelongsToMany
    {
        return $this->belongsToMany(Defecto::class, 'mantenimiento_defecto')
            ->withPivot('nivel_gravedad')
            ->withTimestamps();
    }

    /**
     * Relación: Un mantenimiento tiene muchas imágenes
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(MantenimientoImagen::class)->orderBy('orden');
    }
}
