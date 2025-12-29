<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    /**
     * Listar roles con sus permisos
     */
    public function index(): Response
    {
        $roles = Role::query()
            ->withCount('users')
            ->whereIn('name', ['funcionario', 'visitante'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Actualizar permisos de un rol
     */
    public function updatePermissions(Request $request, Role $role)
    {
        abort(403, 'Los permisos ahora se gestionan por cargos. Los roles solo indican tipo de usuario (funcionario/visitante).');
    }
}
