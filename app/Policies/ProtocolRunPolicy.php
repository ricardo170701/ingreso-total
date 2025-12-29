<?php

namespace App\Policies;

use App\Models\ProtocolRun;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProtocolRunPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_protocolo');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProtocolRun $protocolRun): bool
    {
        return $user->hasPermission('view_protocolo');
    }

    /**
     * Determine whether the user can create models (activate emergency protocol).
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('protocol_emergencia_open_all');
    }

    /**
     * Determine whether the user can activate the emergency protocol.
     */
    public function activateEmergency(User $user): bool
    {
        return $user->hasPermission('protocol_emergencia_open_all');
    }
}
