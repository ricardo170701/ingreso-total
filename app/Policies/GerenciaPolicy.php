<?php

namespace App\Policies;

use App\Models\Gerencia;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GerenciaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_departamentos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Gerencia $gerencia): bool
    {
        return $user->hasPermission('view_departamentos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_departamentos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Gerencia $gerencia): bool
    {
        return $user->hasPermission('edit_departamentos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Gerencia $gerencia): bool
    {
        return $user->hasPermission('delete_departamentos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Gerencia $gerencia): bool
    {
        return $user->hasPermission('delete_departamentos');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Gerencia $gerencia): bool
    {
        return $user->hasPermission('delete_departamentos');
    }
}
