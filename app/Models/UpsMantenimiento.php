<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpsMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'ups_mantenimientos';

    protected $fillable = [
        'ups_id',
        'fecha_mantenimiento',
        'fecha_fin_programada',
        'tipo',
        'descripcion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_mantenimiento' => 'date',
        'fecha_fin_programada' => 'date',
    ];

    public function ups()
    {
        return $this->belongsTo(Ups::class, 'ups_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documentos()
    {
        return $this->hasMany(UpsMantenimientoDocumento::class, 'ups_mantenimiento_id')->orderBy('orden');
    }

    public function imagenes()
    {
        return $this->hasMany(UpsMantenimientoImagen::class, 'ups_mantenimiento_id')->orderBy('orden');
    }
}
