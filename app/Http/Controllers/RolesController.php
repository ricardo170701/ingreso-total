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
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->where('activo', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        // Agrupar permisos por grupo para el frontend
        $permissionsGrouped = $permissions->groupBy('group')->toArray();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'permissionsGrouped' => $permissionsGrouped,
        ]);
    }

    /**
     * Actualizar permisos de un rol
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route('roles.index')
            ->with('message', 'Permisos actualizados exitosamente para el rol ' . $role->name);
    }
}
