<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportePolicy
{
    /**
     * Determine whether the user can view reports.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_reportes');
    }

    /**
     * Determine whether the user can export users report.
     */
    public function exportUsers(User $user): bool
    {
        return $user->hasPermission('view_reportes') && $user->hasPermission('view_users');
    }

    /**
     * Determine whether the user can export accesos report.
     */
    public function exportAccesos(User $user): bool
    {
        return $user->hasPermission('view_reportes');
    }

    /**
     * Determine whether the user can export mantenimientos report.
     */
    public function exportMantenimientos(User $user): bool
    {
        return $user->hasPermission('view_reportes') && $user->hasPermission('view_mantenimientos');
    }

    /**
     * Determine whether the user can export puertas report.
     */
    public function exportPuertas(User $user): bool
    {
        return $user->hasPermission('view_reportes') && $user->hasPermission('view_puertas');
    }
}
