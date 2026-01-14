<?php

namespace App\Policies;

use App\Models\TarjetaNfc;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TarjetaNfcPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_tarjetas_nfc');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TarjetaNfc $tarjetaNfc): bool
    {
        return $user->hasPermission('view_tarjetas_nfc');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_tarjetas_nfc');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TarjetaNfc $tarjetaNfc): bool
    {
        return $user->hasPermission('edit_tarjetas_nfc');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TarjetaNfc $tarjetaNfc): bool
    {
        return $user->hasPermission('delete_tarjetas_nfc');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TarjetaNfc $tarjetaNfc): bool
    {
        return $user->hasPermission('delete_tarjetas_nfc');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TarjetaNfc $tarjetaNfc): bool
    {
        return $user->hasPermission('delete_tarjetas_nfc');
    }
}
