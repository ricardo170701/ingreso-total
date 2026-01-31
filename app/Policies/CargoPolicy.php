<?php

namespace App\Policies;

use App\Models\Cargo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CargoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_cargos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cargo $cargo): bool
    {
        return $user->hasPermission('view_cargos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_cargos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cargo $cargo): bool
    {
        return $user->hasPermission('edit_cargos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cargo $cargo): bool
    {
        return $user->hasPermission('delete_cargos');
    }

    /**
     * Determine whether the user can manage permissions (guardar permisos del sistema del cargo).
     * Basta con edit_cargos; la restricción de "permiso superior" aplica a ver/asignar cargos, no a editar permisos del cargo actual.
     */
    public function managePermissions(User $user, Cargo $cargo): bool
    {
        return $user->hasPermission('edit_cargos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cargo $cargo): bool
    {
        return $user->hasPermission('delete_cargos');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cargo $cargo): bool
    {
        return $user->hasPermission('delete_cargos');
    }
}
