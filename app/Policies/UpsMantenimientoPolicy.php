<?php

namespace App\Policies;

use App\Models\UpsMantenimiento;
use App\Models\User;

class UpsMantenimientoPolicy
{
    public function viewAny(User $user): bool
    {
        // Ver UPS implica poder ver sus mantenimientos
        return $user->hasPermission('view_ups');
    }

    public function view(User $user, UpsMantenimiento $mantenimiento): bool
    {
        return $user->hasPermission('view_ups');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_ups_mantenimientos');
    }

    public function update(User $user, UpsMantenimiento $mantenimiento): bool
    {
        return $user->hasPermission('edit_ups_mantenimientos');
    }

    public function delete(User $user, UpsMantenimiento $mantenimiento): bool
    {
        return $user->hasPermission('delete_ups_mantenimientos');
    }
}


