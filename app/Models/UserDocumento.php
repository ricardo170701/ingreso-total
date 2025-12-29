<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocumento extends Model
{
    use HasFactory;

    protected $table = 'user_documentos';

    protected $fillable = [
        'user_id',
        'tipo',
        'tipo_contrato',
        'nombre_original',
        'mime',
        'size',
        'path',
        'subido_por',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
