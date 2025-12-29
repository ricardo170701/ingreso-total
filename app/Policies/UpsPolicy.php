<?php

namespace App\Policies;

use App\Models\Ups;
use App\Models\User;

class UpsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_ups');
    }

    public function view(User $user, Ups $ups): bool
    {
        return $user->hasPermission('view_ups');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_ups');
    }

    public function update(User $user, Ups $ups): bool
    {
        return $user->hasPermission('edit_ups');
    }

    public function delete(User $user, Ups $ups): bool
    {
        return $user->hasPermission('delete_ups');
    }
}


