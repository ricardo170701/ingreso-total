<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Gerencia;
use App\Models\Permission;
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
        'cargo_texto',
        'es_discapacitado',
        'nombre',
        'apellido',
        'n_identidad',
        'observaciones',
        'gerencia_id',
        'foto_perfil',
        'activo',
        'fecha_expiracion',
        'tipo_contrato',
        'nombre_empresa',
        'creado_por',
        'created_by',
        'updated_by',
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
     * Relación: Un usuario pertenece a una gerencia
     */
    public function gerencia()
    {
        return $this->belongsTo(Gerencia::class);
    }

    /**
     * Relación: Un usuario tiene muchos códigos QR
     */
    public function codigosQr()
    {
        return $this->hasMany(CodigoQr::class);
    }

    /**
     * Relación: Un usuario tiene muchas tarjetas NFC
     */
    public function tarjetasNfc()
    {
        return $this->hasMany(TarjetaNfc::class);
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
     * Auditoría (nuevo): usuario que creó este usuario (created_by)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Auditoría (nuevo): usuario que editó por última vez este usuario (updated_by)
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relación: usuarios creados por este usuario
     */
    public function usuariosCreados()
    {
        return $this->hasMany(User::class, 'creado_por');
    }

    /**
     * Documentos asociados al usuario (ej: contratos PDF)
     */
    public function documentos()
    {
        return $this->hasMany(UserDocumento::class)->latest();
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
     * Ahora verifica por piso: si el usuario tiene permiso al piso de la puerta, tiene acceso
     */
    public function tieneAccesoAPuerta(Puerta|int $puerta): bool
    {
        if (!$this->cargo) {
            return false;
        }

        $puertaModel = $puerta instanceof Puerta ? $puerta : Puerta::query()->with('piso')->find($puerta);

        if (!$puertaModel) {
            return false;
        }

        // Si la puerta requiere discapacidad, el usuario debe ser discapacitado (además de tener permiso)
        if ($puertaModel->requiere_discapacidad && !$this->es_discapacitado) {
            return false;
        }

        // Si la puerta es solo servidores públicos, el usuario debe ser servidor público o proveedor
        if ($puertaModel->solo_servidores_publicos) {
            $staffRoles = ['servidor_publico', 'proveedor', 'funcionario'];
            $roleName = $this->role?->name ?? null;
            if (!in_array($roleName, $staffRoles, true)) {
                return false;
            }
        }

        // Solo se verifica permiso por puerta (cargo_puerta_acceso)
        return $this->cargo->puertas()->whereKey($puertaModel->id)->exists();
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permissionName): bool
    {
        // Los permisos vienen del cargo (roles solo representan tipo de usuario: funcionario/visitante)
        if (!$this->cargo) {
            return false;
        }

        return $this->cargo->hasPermission($permissionName);
    }

    /**
     * Obtener todos los permisos del usuario a través de su cargo
     */
    public function getPermissionsAttribute(): array
    {
        // Los permisos vienen del cargo (roles solo representan tipo de usuario: funcionario/visitante)
        if (!$this->relationLoaded('cargo')) {
            $this->load('cargo');
        }

        if (!$this->cargo) {
            return [];
        }

        // Cargar permisos del cargo si no están cargados
        if (!$this->cargo->relationLoaded('permissions')) {
            $this->cargo->load('permissions');
        }

        return $this->cargo->permissions
            ->where('activo', true)
            ->pluck('name')
            ->toArray();
    }
}
