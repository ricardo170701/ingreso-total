<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
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

        try {
            $user = $this->authService->validateCredentials($data['email'], $data['password']);

            if (!$user) {
                return back()->withErrors([
                    'email' => 'Credenciales inválidas.',
                ])->onlyInput('email');
            }

            // Autenticar con sesión (en lugar de token)
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Usuario inactivo.') {
                return back()->withErrors([
                    'email' => 'Usuario inactivo.',
                ])->onlyInput('email');
            }
            throw $e;
        }
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
}
