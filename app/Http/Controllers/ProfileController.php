<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Mostrar el perfil del usuario autenticado
     */
    public function show(Request $request): Response
    {
        $user = $request->user()->load(['role', 'cargo', 'departamento.piso']);

        return Inertia::render('Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'n_identidad' => $user->n_identidad,
                'foto_perfil' => $user->foto_perfil,
                'activo' => $user->activo,
                'es_discapacitado' => $user->es_discapacitado,
                'fecha_expiracion' => $user->fecha_expiracion?->format('Y-m-d'),
                'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                'cargo' => $user->cargo ? ['id' => $user->cargo->id, 'name' => $user->cargo->name] : null,
                'departamento' => $user->departamento ? [
                    'id' => $user->departamento->id,
                    'nombre' => $user->departamento->nombre,
                    'piso' => $user->departamento->piso ? ['id' => $user->departamento->piso->id, 'nombre' => $user->departamento->piso->nombre] : null,
                ] : null,
                'created_at' => $user->created_at?->format('d/m/Y H:i'),
                'updated_at' => $user->updated_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Actualizar el perfil del usuario autenticado
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Actualizar nombre y apellido
        if (isset($data['nombre'])) {
            $user->nombre = $data['nombre'];
        }
        if (isset($data['apellido'])) {
            $user->apellido = $data['apellido'];
        }

        // Actualizar el campo 'name' si se cambió nombre o apellido
        $nombre = trim($user->nombre ?? '');
        $apellido = trim($user->apellido ?? '');
        if ($nombre || $apellido) {
            $user->name = trim($nombre . ' ' . $apellido) ?: ($user->username ?? $user->email);
        }

        // Actualizar foto de perfil si se subió una nueva
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            // Guardar nueva foto
            $path = $request->file('foto')->store('fotos_perfil', 'public');
            $user->foto_perfil = $path;
        }

        // Actualizar contraseña si se proporcionó
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}

