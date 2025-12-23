<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'cargo_id',
        'es_discapacitado',
        // Datos de perfil (según lo conversado)
        'username',
        'nombre',
        'apellido',
        'departamento',
        'foto_perfil',
        'activo',
        'fecha_expiracion',
        'creado_por',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'es_discapacitado' => 'boolean',
        'activo' => 'boolean',
        'fecha_expiracion' => 'date',
    ];

    /**
     * Relación: Un usuario pertenece a un rol
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación: Un usuario pertenece a un cargo
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    /**
     * Relación: Un usuario tiene muchos códigos QR
     */
    public function codigosQr()
    {
        return $this->hasMany(CodigoQr::class);
    }

    /**
     * Relación: Un usuario tiene muchos accesos
     */
    public function accesos()
    {
        return $this->hasMany(Acceso::class);
    }

    /**
     * Relación: usuario creador (quién registró al usuario)
     */
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación: usuarios creados por este usuario
     */
    public function usuariosCreados()
    {
        return $this->hasMany(User::class, 'creado_por');
    }

    /**
     * Visitas donde este usuario es el visitante
     */
    public function visitasComoVisitante()
    {
        return $this->hasMany(Visita::class, 'visitante_id');
    }

    /**
     * Visitas donde este usuario es el anfitrión
     */
    public function visitasComoAnfitrion()
    {
        return $this->hasMany(Visita::class, 'empleado_anfitrion');
    }

    /**
     * Visitas registradas por este usuario (operador)
     */
    public function visitasComoOperador()
    {
        return $this->hasMany(Visita::class, 'operador_registro');
    }

    /**
     * Obtener las puertas a las que tiene acceso el usuario a través de su cargo
     * Este es un método helper que accede a las puertas del cargo del usuario
     */
    public function getPuertas()
    {
        if (!$this->cargo) {
            return collect();
        }

        return $this->cargo->puertas;
    }

    /**
     * Verificar si el usuario tiene acceso a una puerta específica
     */
    public function tieneAccesoAPuerta(Puerta|int $puerta): bool
    {
        if (!$this->cargo) {
            return false;
        }

        $puertaModel = $puerta instanceof Puerta ? $puerta : Puerta::query()->find($puerta);

        if (!$puertaModel) {
            return false;
        }

        // Si la puerta requiere discapacidad, el usuario debe ser discapacitado (además de tener permiso)
        if ($puertaModel->requiere_discapacidad && !$this->es_discapacitado) {
            return false;
        }

        return $this->cargo->puertas()->whereKey($puertaModel->getKey())->exists();
    }
}
