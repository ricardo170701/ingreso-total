<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Mostrar el formulario de login
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Procesar el login (reutiliza la lógica de la API pero con sesiones)
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->authService->validateCredentials($data['email'], $data['password']);

        if (!$user) {
            $errorMessage = $this->authService->getLastError() ?? 'Credenciales inválidas.';
            return back()->withErrors([
                'email' => $errorMessage,
            ])->onlyInput('email');
        }

        // Autenticar con sesión (en lugar de token)
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Si el usuario no ha cambiado su contraseña, forzar cambio
        if (is_null($user->password_changed_at)) {
            return redirect()->route('password.change');
        }

        // Visitantes: acceso limitado a Ingreso/Soporte
        if (($user->role?->name ?? null) === 'visitante') {
            return redirect()->route('ingreso.index');
        }

        // Evaluar permisos: si tiene permiso de ingreso, redirigir a ingreso, sino al perfil
        if ($user->hasPermission('view_ingreso')) {
            return redirect()->route('ingreso.index');
        }

        return redirect()->route('profile.show');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Mostrar formulario de cambio de contraseña obligatorio
     */
    public function showChangePasswordForm()
    {
        $user = Auth::user();
        
        // Si ya cambió la contraseña, evaluar permisos para redirigir
        if ($user && !is_null($user->password_changed_at)) {
            // Visitantes: acceso limitado a Ingreso/Soporte - redirigir directamente a ingreso
            if (($user->role?->name ?? null) === 'visitante') {
                return redirect()->route('ingreso.index');
            }
            
            // Evaluar permisos: si tiene permiso de ingreso, redirigir a ingreso, sino al perfil
            if ($user->hasPermission('view_ingreso')) {
                return redirect()->route('ingreso.index');
            }
            return redirect()->route('profile.show');
        }

        return Inertia::render('Auth/ChangePassword');
    }

    /**
     * Procesar cambio de contraseña obligatorio
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $user = $request->user();
        
        // Actualizar contraseña
        $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        $user->password_changed_at = now();
        $user->save();

        // Visitantes: acceso limitado a Ingreso/Soporte
        if (($user->role?->name ?? null) === 'visitante') {
            return redirect()->route('ingreso.index')
                ->with('success', 'Contraseña actualizada correctamente. Bienvenido.');
        }

        // Evaluar permisos: si tiene permiso de ingreso, redirigir a ingreso, sino al perfil
        if ($user->hasPermission('view_ingreso')) {
            return redirect()->route('ingreso.index')
                ->with('success', 'Contraseña actualizada correctamente. Bienvenido.');
        }

        return redirect()->route('profile.show')
            ->with('success', 'Contraseña actualizada correctamente. Bienvenido.');
    }
}
