<?php

namespace App\Http\Middleware;

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
            ],
        ];
    }
}
