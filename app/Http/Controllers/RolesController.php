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
            // Tipos de vinculación (compatibilidad: 'funcionario' legado)
            ->whereIn('name', ['visitante', 'servidor_publico', 'contratista', 'funcionario'])
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
        abort(403, 'Los permisos se gestionan por Roles (antes Cargos). Los tipos de vinculación solo indican si el usuario es visitante/servidor público/contratista.');
    }
}
