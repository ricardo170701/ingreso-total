<?php

namespace App\Policies;

use App\Models\Puerta;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PuertaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_puertas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('view_puertas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_puertas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('edit_puertas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('delete_puertas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('delete_puertas');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('delete_puertas');
    }

    /**
     * Determine whether the user can toggle (open/close) the door.
     */
    public function toggle(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('toggle_puertas');
    }

    /**
     * Determine whether the user can reboot the door's Raspberry Pi.
     */
    public function reboot(User $user, Puerta $puerta): bool
    {
        return $user->hasPermission('reboot_puertas');
    }
}
