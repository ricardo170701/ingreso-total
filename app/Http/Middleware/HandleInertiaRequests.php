<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? (function ($user) {
                    // Fail-safe: si el usuario ya expiró, marcarlo inactivo inmediatamente
                    // (evita que siga activo hasta que corra el scheduler/cron).
                    try {
                        if (($user->activo ?? true) && $user->fecha_expiracion) {
                            $hoy = Carbon::now()->startOfDay();
                            $exp = Carbon::parse($user->fecha_expiracion)->startOfDay();
                            if ($exp->lt($hoy)) {
                                DB::table('users')->where('id', $user->id)->update([
                                    'activo' => false,
                                    'updated_at' => now(),
                                ]);
                                $user->activo = false;
                            }
                        }
                    } catch (\Throwable $e) {
                        // No bloquear la carga de la app por un error de actualización
                    }

                    // Cargar relaciones necesarias para obtener permisos
                    if (!$user->relationLoaded('role')) {
                        $user->load('role');
                    }
                    if (!$user->relationLoaded('cargo')) {
                        $user->load('cargo');
                    }
                    if ($user->cargo && !$user->cargo->relationLoaded('permissions')) {
                        $user->cargo->load('permissions');
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'foto_perfil' => $user->foto_perfil,
                        'activo' => (bool) ($user->activo ?? true),
                        'fecha_expiracion' => $user->fecha_expiracion ? Carbon::parse($user->fecha_expiracion)->format('Y-m-d') : null,
                        'role' => $user->role ? [
                            'id' => $user->role->id,
                            'name' => $user->role->name,
                        ] : null,
                        'cargo' => $user->cargo ? [
                            'id' => $user->cargo->id,
                            'name' => $user->cargo->name,
                        ] : null,
                        'permissions' => $user->permissions ?? [],
                    ];
                })($request->user()) : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'message' => session('message'),
            ],
        ];
    }
}
