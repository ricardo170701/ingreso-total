<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Valida las credenciales y retorna el usuario si son válidas.
     *
     * @param string $email
     * @param string $password
     * @return User|null Retorna el usuario si las credenciales son válidas, null si no
     * @throws \Exception Si el usuario está inactivo
     */
    public function validateCredentials(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (isset($user->activo) && $user->activo === false) {
            throw new \Exception('Usuario inactivo.');
        }

        return $user;
    }
}
