<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'puerta_id',
        'fecha_mantenimiento',
        'fecha_fin_programada',
        'tipo',
        'falla',
        'created_by',
        'updated_by',
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
     * Relación: Un mantenimiento tiene muchos documentos (PDFs)
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(MantenimientoDocumento::class)->orderBy('orden');
    }

    /**
     * Relación: Usuario que creó el mantenimiento
     */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación: Usuario que editó por última vez el mantenimiento
     */
    public function editadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
