<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProtocolRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo',
        'duration_seconds',
        'estado',
        'total_puertas',
        'puertas_exitosas',
        'puertas_fallidas',
        'observaciones',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'total_puertas' => 'integer',
        'puertas_exitosas' => 'integer',
        'puertas_fallidas' => 'integer',
    ];

    /**
     * Relación: Un protocol run pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un protocol run tiene muchos items (una por puerta)
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProtocolRunItem::class);
    }
}
