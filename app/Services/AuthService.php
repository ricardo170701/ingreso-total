<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Mensaje de error de la última validación
     */
    private ?string $lastError = null;

    /**
     * Valida las credenciales y retorna el usuario si son válidas.
     *
     * @param string $email
     * @param string $password
     * @return User|null Retorna el usuario si las credenciales son válidas, null si no
     */
    public function validateCredentials(string $email, string $password): ?User
    {
        $this->lastError = null;

        $user = User::query()->where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            $this->lastError = 'Credenciales inválidas.';
            return null;
        }

        // Primero verificar el estatus activo
        if (isset($user->activo) && $user->activo === false) {
            $this->lastError = 'Usuario inactivo.';
            return null;
        }

        // Si el usuario está activo, verificar la fecha de expiración
        if ($user->activo && $user->fecha_expiracion) {
            $fechaExpiracion = \Carbon\Carbon::parse($user->fecha_expiracion)->startOfDay();
            $hoy = \Carbon\Carbon::now()->startOfDay();

            // Si la fecha de expiración ha pasado, actualizar el estatus a inactivo
            if ($fechaExpiracion->lt($hoy)) {
                $user->activo = false;
                $user->save();
                $this->lastError = 'Usuario inactivo por fecha de expiración.';
                return null;
            }
        }

        return $user;
    }

    /**
     * Obtiene el mensaje de error de la última validación
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
